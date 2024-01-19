<?php

namespace App\Http\Controllers\Report;

use App\Click;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
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

}
