<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\PayoutLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use LeadMax\TrackYourStats\System\Session;

class PayoutLogController extends Controller
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

	public function updateLogData(PayoutLog $payoutLog, Request $request) {
		$keys = collect($request->all())->keys();

		$payoutLog->update($keys->combine($request->all())->toArray());

		return response()->json(['success' => true]);
	}

	private function reportPayout()
	{
		if (Session::userType() == 3) {
			$report = DB::table('payout_logs')
			            ->where('payout_logs.user_id', Session::userID())
			            ->join('rep', 'rep.idrep', '=', 'payout_logs.user_id')
			            ->select(
				            'payout_logs.revenue',
				            'payout_logs.start_of_week',
				            'payout_logs.end_of_week',
				            'payout_logs.status',
				            'payout_logs.payout_type',
			            )->orderBy('payout_logs.start_of_week', 'desc')
			            ->paginate(100);
		} else {
			$report = DB::table('payout_logs')
			            ->join('rep', 'rep.idrep', '=', 'payout_logs.user_id')
			            ->select(
				            'payout_logs.id as log_id',
				            'rep.user_name',
				            'payout_logs.revenue',
				            'payout_logs.start_of_week',
				            'payout_logs.end_of_week',
				            'payout_logs.status',
				            'payout_logs.payout_type',
				            'payout_logs.payout_id',
				            'payout_logs.country'
			            )->orderBy('payout_logs.start_of_week', 'desc')
			            ->paginate(100);
		}

		return $report;
	}
}
