<?php

namespace App\Http\Controllers\Report;

use App\Privilege;
use App\Offer;
use App\Services\Repositories\Offer\OfferAffiliateClicksRepository;
use Carbon\Carbon;
use LeadMax\TrackYourStats\Report\Affiliate;
use LeadMax\TrackYourStats\Report\Reporter;
use LeadMax\TrackYourStats\Report\Repositories\Offer\AdminOfferRepository;
use LeadMax\TrackYourStats\Report\Repositories\Offer\AffiliateOfferRepository;
use LeadMax\TrackYourStats\Report\Repositories\Offer\ManagerOfferRepository;
use LeadMax\TrackYourStats\System\Session;

use LeadMax\TrackYourStats\Report\Filters;
use LeadMax\TrackYourStats\Report\Repositories\Offer\GodOfferRepository;

class OfferReportController extends ReportController
{

    public function god() {
        $dates = self::getDates();
        $repo = new GodOfferRepository(\DB::getPdo());

        $reporter = new Reporter($repo);


        $reporter
            ->addFilter(new Filters\DeductionColumnFilter())
            ->addFilter(new Filters\Total([
                'Clicks', 
                'UniqueClicks', 
                'FreeSignUps', 
                'PendingConversions', 
                'Conversions', 
                'Revenue', 
                'Deductions'
            ]))
            ->addFilter(new Filters\EarningPerClick('UniqueClicks', 'Revenue'))
            ->addFilter(new Filters\DollarSign(['Revenue', 'Deductions', 'EPC']))
            ->addFilter(new Filters\ClickLink(request()));

        return view('report.offer.admin', compact('reporter', 'dates'));
    }

    public function admin()
    {
        $dates = self::getDates();
        $repo = Session::permissions()->can('view_all_users') ?
	        new GodOfferRepository(\DB::getPdo())
	        :
	        new AdminOfferRepository(\DB::getPdo());

        $reporter = new Reporter($repo);


        $reporter
            ->addFilter(new Filters\DeductionColumnFilter())
            ->addFilter(new Filters\Total([
                'Clicks', 
                'UniqueClicks', 
                'FreeSignUps', 
                'PendingConversions', 
                'Conversions', 
                'Revenue', 
                'Deductions'
            ]))
            ->addFilter(new Filters\EarningPerClick('UniqueClicks', 'Revenue'))
            ->addFilter(new Filters\DollarSign(['Revenue', 'Deductions', 'EPC']))
            ->addFilter(new Filters\ClickLink(request()));

        return view('report.offer.admin', compact('reporter', 'dates'));
    }

    public function manager()
    {
        $dates = self::getDates();
        $repo = new ManagerOfferRepository(\LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance());

        $reporter = new Reporter($repo);


        $reporter
            ->addFilter(new Filters\DeductionColumnFilter())
            ->addFilter(new Filters\Total(['Clicks', 'UniqueClicks', 'FreeSignUps', 'PendingConversions', 'Conversions']))
            ->addFilter(new Filters\EarningPerClick('UniqueClicks', 'Revenue'))
            ->addFilter(new Filters\ClickLink(request()));

        return view('report.offer.admin', compact('reporter', 'dates'));
    }

    public function affiliate()
    {
        $dates = self::getDates();
        $report = new Affiliate();
        $report->fetchBonuses($dates['startDate'], $dates['endDate']);

        $repo = new AffiliateOfferRepository(\DB::getPdo());
        $repo->setAffiliateId(Session::userID());

        $reporter = new Reporter($repo);

        $reporter
            ->addFilter(new Filters\DeductionColumnFilter())
            ->addFilter(new Filters\Total(['Clicks', 'UniqueClicks', 'FreeSignUps', 'PendingConversions', 'Conversions', 'Revenue', 'Deductions', 'TOTAL'], ['Revenue', 'Deductions']))
            ->addFilter(new Filters\EarningPerClick('UniqueClicks', 'Revenue'))
            ->addFilter(new Filters\DollarSign(['Revenue', 'Deductions', 'EPC', 'TOTAL']));

        if (\request()->expectsJson()) {
            return response($reporter->fetchReport($dates['startDate'], $dates['endDate']));
        }

        return view('report.offer.affiliate', compact('reporter', 'report', 'dates'));
    }

    public function show()
    {
        switch (Session::userType()) {
            case Privilege::ROLE_GOD:
                return $this->god();
                
            case Privilege::ROLE_ADMIN:
                return $this->admin();

            case Privilege::ROLE_MANAGER:
                return $this->manager();

            case Privilege::ROLE_AFFILIATE:
                return $this->affiliate();

            default:
                return redirect('/');
        }
    }

    public function showConversionsByUser(Offer $offer) {

		$dates = self::getDates();
		//$offer = Offer::findOrFail($offerId);

		$start = Carbon::parse( $dates['startDate'], 'America/New_York' );
		$end   = Carbon::parse( $dates['endDate'], 'America/New_York' );

		$affiliateRepo = new OfferAffiliateClicksRepository( $offer->idoffer, Session::user() );
		$affiliateReport = $affiliateRepo->between( $start, $end );

		return view('report.offer.conversions', compact('affiliateReport', 'offer'));
	}

    public function showConversionsByCountry(Offer $offer) {
		$dates = self::getDates();

        $start = Carbon::parse( $dates['startDate'], 'America/New_York' );
		$end   = Carbon::parse( $dates['endDate'], 'America/New_York' );

        $affiliateRepo = new OfferAffiliateClicksRepository( $offer->idoffer, Session::user() );
		$affiliateReport = $affiliateRepo->getOfferConversionsByCountry( $start, $end );

        return view('report.offer.conversions-by-country', compact('affiliateReport', 'offer'));

	}

}
