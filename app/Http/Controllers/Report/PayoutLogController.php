<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\PayoutLog;
use App\Services\PayoutLogService;
use Exception;
use Illuminate\Http\Request;
use LeadMax\TrackYourStats\System\Session;

class PayoutLogController extends Controller
{
	public function show(): \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application {

		if(Session::userType() == 3) {
			return view('report.payout.affiliate');
		}

		return view('report.payout.show');
	}

	/**
	 * @throws Exception
	 */
	public function get(PayoutLogService $payout_log_service): \Illuminate\Http\JsonResponse {
		$reports = $payout_log_service->reportPayout();

		return response()->json($reports->original);
	}

	public function markStatusPaid(PayoutLog $payoutLog): \Illuminate\Http\JsonResponse {
		$payoutLog->update(['status' => 'paid']);

		return response()->json(['success' => true]);
	}

	public function updateLogData(PayoutLog $payoutLog, Request $request): \Illuminate\Http\JsonResponse {
		$keys = collect($request->all())->keys();

		$payoutLog->update($keys->combine($request->all())->toArray());

		return response()->json(['success' => true]);
	}

}
