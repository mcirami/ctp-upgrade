<?php

namespace App\Http\Controllers\Report;

use App\Privilege;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use LeadMax\TrackYourStats\Report\Affiliate;
use LeadMax\TrackYourStats\Report\Reporter;
use LeadMax\TrackYourStats\Report\Repositories\Offer\AdminOfferRepository;
use LeadMax\TrackYourStats\Report\Repositories\Offer\AffiliateOfferRepository;
use LeadMax\TrackYourStats\Report\Repositories\Offer\ManagerOfferRepository;
use LeadMax\TrackYourStats\System\Session;

use LeadMax\TrackYourStats\Report\Filters;

class OfferReportController extends ReportController
{


    public function admin()
    {
        $dates = self::getDates();
        $repo = new AdminOfferRepository(\DB::getPdo());

        $reporter = new Reporter($repo);


        $reporter
            ->addFilter(new Filters\DeductionColumnFilter())
            ->addFilter(new Filters\Total(['Clicks', 'UniqueClicks', 'FreeSignUps', 'PendingConversions', 'Conversions', 'Revenue', 'Deductions']))
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
            ->addFilter(new Filters\Total(['Clicks', 'UniqueClicks', 'FreeSignUps', 'PendingConversions', 'Conversions', 'Revenue', 'Deductions']))
            ->addFilter(new Filters\EarningPerClick('UniqueClicks', 'Revenue'))
            ->addFilter(new Filters\DollarSign(['Revenue', 'Deductions', 'EPC']))
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


}
