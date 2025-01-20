<?php

namespace App\Http\Controllers\Report;

use App\Click;
use App\Offer;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use LeadMax\TrackYourStats\Report\Reporter;
use LeadMax\TrackYourStats\Report\Repositories\SubVarRepository;
use LeadMax\TrackYourStats\Report\Filters;
use phpDocumentor\Reflection\Types\Object_;

class SubReportController extends ReportController
{


    public function show()
    {
        $dates = self::getDates();

        $repo = new SubVarRepository(\DB::getPdo());

        $repo->setSubNumber(request()->query('sub', 1));


        $reporter = new Reporter($repo);

        $reporter
            ->addFilter(new Filters\Total(['clicks','unique','conversions','revenue']))
            ->addFilter(new Filters\EarningPerClick('unique', 'revenue'))
            ->addFilter(new Filters\DollarSign(['EPC', 'revenue', 'TOTAL', 'Total']));

        return view('report.sub', compact('reporter', 'dates'));
    }

	public function showSubConversions(Request $request): \Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\Foundation\Application {

		$subID = $request->get('subid');
		$dates = self::getDates();

		$subReport = DB::table('click_vars')
		  ->where('sub1', '=', $subID)
		  ->orWhere('sub2', '=', $subID)
		  ->orWhere('sub3', '=', $subID)
		  ->leftJoin('clicks', 'clicks.idclicks', '=', 'click_vars.click_id')
		  ->leftJoin('offer', 'clicks.offer_idoffer', '=', 'offer.idoffer')
		  ->join('conversions', function($query) use($dates) {
			  $query->on('conversions.click_id', '=', 'clicks.idclicks')->whereBetween('conversions.timestamp', [$dates['startDate'], $dates['endDate']]);
		  })
		  ->select('conversions.paid', 'conversions.timestamp', 'offer.offer_name')->orderBy('offer.offer_name')->get();

		return view ('report.single-sub', compact('subReport', 'subID'));
	}

	public function showUserConversionsBySubId($userId, $offer) {
		$dates = self::getDates();
		$startDate = $dates['originalStart'];
		$endDate = $dates['originalEnd'];
		$dateSelect = request()->query('dateSelect');
		$offerName = Offer::findOrFail($offer)->offer_name;
		$user = User::findOrFail($userId);

		$report = Click::where('rep_idrep', '=', $userId)
			->whereBetween('clicks.first_timestamp', [$dates['startDate'], $dates['endDate']])
			->where('clicks.offer_idoffer', '=', $offer)
			->where('clicks.click_type', '!=', 2)
			->leftJoin('click_vars', 'click_vars.click_id', '=', 'clicks.idclicks')
			->leftJoin('conversions', 'conversions.click_id', '=', 'clicks.idclicks')
			->select(
				('click_vars.sub1'),
				DB::raw('COUNT(conversions.id) as conversions'),
				DB::raw('COUNT(clicks.idclicks) as clicks')
			)
			->groupBy('click_vars.sub1')
			->orderBy('conversions', 'DESC')
			->paginate(100);

			return view ('report.conversions.affiliate-by-sub', 
			compact(
				'report',
				 'startDate', 
				 'endDate', 
				 'dateSelect',
				 'offerName',
				 'user'
				));
	}

	public function showSubIdClicks($userId, $subId) {
		$dates = self::getDates();
		$startDate = $dates['originalStart'];
		$endDate = $dates['originalEnd'];
		$dateSelect = request()->query('dateSelect');

        $user = User::myUsers()->findOrFail($userId);

	    $reportCollection = Click::where('rep_idrep', '=', $userId)
	                ->where('clicks.click_type', '!=', 2)
	                ->whereBetween('clicks.first_timestamp', [$dates['startDate'], $dates['endDate']])
	                ->leftJoin('click_vars', function($join) use ($subId) {
						$join->on('click_vars.click_id', '=', 'clicks.idclicks')
							->where('click_vars.sub1', '=', $subId);
	                    
	                })
	                ->leftJoin('click_geo', 'click_geo.click_id', '=', 'clicks.idclicks')
	                ->leftJoin('conversions', 'conversions.click_id', '=', 'clicks.idclicks')
	                ->leftJoin('offer', 'offer.idoffer', '=', 'clicks.offer_idoffer')
	                ->select(
						'clicks.idclicks',
						'clicks.first_timestamp as timestamp',
						'offer.offer_name',
						'conversions.timestamp as conversion_timestamp',
						'conversions.paid as paid',
						'click_vars.url',
						'click_vars.sub1',
						'click_vars.sub2',
		                'click_vars.sub3',
						'clicks.referer',
						'click_geo.ip  as ip_address',
						'clicks.offer_idoffer  as offer_id'
	                )
	                ->orderBy('paid', 'DESC')->paginate(100);

		$report = $this->formatResults($reportCollection);

		dd($report);
        return view('report.clicks.affiliate', 
		compact(
			'report', 
			'user', 
			'reportCollection', 
			'startDate', 
			'endDate', 
			'dateSelect'
		));
	}
}
