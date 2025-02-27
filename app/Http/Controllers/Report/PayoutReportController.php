<?php

namespace App\Http\Controllers\Report;


use App\PayoutLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
/*
use Barryvdh\Snappy\Facades\SnappyPdf;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use LeadMax\TrackYourStats\Report\AffiliatePayout;
use Illuminate\Support\Arr;
use LeadMax\TrackYourStats\Report\Filters\DeductionColumnFilter;
use LeadMax\TrackYourStats\Report\Filters\DollarSign;
use LeadMax\TrackYourStats\Report\Filters\Total;
use LeadMax\TrackYourStats\Report\Reporter;
use LeadMax\TrackYourStats\Report\Repositories\Offer\AffiliateOfferRepository;
use LeadMax\TrackYourStats\Report\Repositories\PayoutLogRepository;
use LeadMax\TrackYourStats\Report\Filters;
*/
use LeadMax\TrackYourStats\System\Session;


class PayoutReportController extends ReportController
{


    public function report()
    {
        $reports = $this->reportPayout();

		if(Session::userType() == 3) {
			return view('report.payout.affiliate', compact('reports'));
		}

	    return view('report.payout.show', compact('reports'));

    }

	public function markStatusPaid(PayoutLog $payoutLog) {
		$payoutLog->update(['status' => 'paid']);

		return response()->json(['success' => true]);
	}

   /* public function invoice()
    {
        $dates = static::getDates();
        $repo = new AffiliateOfferRepository(\DB::getPdo());
        $repo->setAffiliateId(Session::userID());
        $offerReporter = new Reporter($repo);
        $offerReporter
            ->addFilter(new Filters\DeductionColumnFilter())
            ->addFilter(new Filters\Total(['Clicks', 'UniqueClicks', 'FreeSignUps', 'PendingConversions', 'Conversions', 'Revenue', 'Deductions', 'TOTAL'], ['Revenue', 'Deductions']))
            ->addFilter(new Filters\EarningPerClick('UniqueClicks', 'Revenue'))
            ->addFilter(new Filters\DollarSign(['Revenue', 'Deductions', 'EPC', 'TOTAL']));

        $offerReport = $offerReporter->fetchReport($dates['startDate'], $dates['endDate']);
        $payoutReport = $this->reportPayout();
        $title = strtoupper(Session::user()->user_name) . '_' . $dates['startDate'] . '_THROUGH_' . $dates['endDate'];

        return \PDF::loadView('pdf.payout-log', compact('offerReport', 'dates', 'payoutReport', 'title'))->download($title . '.pdf');
    }


    private function reportPayoutHistory()
    {
        $dates = self::getDates();

        $payoutRepository = new PayoutLogRepository(\DB::getPdo());
        $payoutRepository->setUserId(Session::userID());

        $reporter = new Reporter($payoutRepository);


        $reporter
            ->addFilter(new DeductionColumnFilter('deductions'))
            ->addFilter(new Total([], ['revenue', 'deductions', 'bonuses', 'referrals']))
            ->addFilter(new DollarSign(['revenue', 'deductions', 'bonuses', 'referrals', 'TOTAL']))
            ->addFilter(function ($data) {
                // Remove the total row
                array_pop($data);
                foreach ($data as &$row) {
                    foreach (['start_of_week', 'end_of_week'] as $key) {
                        if (isset($row[$key])) {
                            $row[$key] = Carbon::createFromTimeString($row[$key])->format('Y-m-d');
                        }
                    }
                }


                return $data;
            });


        return $reporter->fetchReport($dates['startDate'], $dates['endDate']);
    }*/

    private function reportPayout()
    {
		if (Session::userType() == 3) {
			return 0;
		} else {
			$report = DB::table('payout_logs')
			            ->leftJoin('payout_data', 'payout_data.rep_idrep' , '=', 'payout_logs.user_id')
						->join('rep', 'rep.idrep', '=', 'payout_logs.user_id')
						->select(
							'payout_logs.id as log_id',
							'rep.user_name',
							'payout_logs.revenue',
							'payout_logs.start_of_week',
							'payout_logs.end_of_week',
							'payout_logs.status',
							'payout_data.payout_type',
							'payout_data.payout_id',
							'payout_data.country'
						)->orderBy('payout_logs.start_of_week', 'desc')
			            ->paginate(100);
		}

        return $report;
    }


}
