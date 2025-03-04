<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\PayoutLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use LeadMax\TrackYourStats\System\Session;
use Yajra\DataTables\DataTables;

class PayoutLogController extends Controller
{
	public function show() {

		/*$reports = $this->reportPayout();

		dd($reports);*/
		return view('report.payout.show');
	}
	public function get()
	{
		$reports = $this->reportPayout();

		if(Session::userType() == 3) {
			return view('report.payout.affiliate', compact('reports'));
		}

		return response()->json($reports->original);
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

	/**
	 * @throws \Exception
	 */
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
			            )->orderBy('payout_logs.start_of_week', 'desc');
		}

		return $this->getDataTablesData($report);
	}

	private function getDataTablesData($query) {
		return DataTables::of($query)
		                 ->editColumn('user_name', function ($row) {
							 return "<span>{$row->user_name}</span>";
						 })->addColumn('payout_dates', function($row){
							// Combine start_of_week & end_of_week
							$start = Carbon::parse($row->start_of_week)->format('m/d/Y');
							$end   = Carbon::parse($row->end_of_week)->format('m/d/Y');
							return "{$start} - {$end}";
						})->editColumn('revenue', function($row){
							// format the currency
							return "$" . number_format($row->revenue, 2, ".", ",");
						})->editColumn('payout_type', function($row){
							// replicate your conditional Blade logic here
							$currentValue = $row->payout_type ?? "No Details";
							// We’ll create the same HTML structure you had:
							return "<div class='edit_details'>
						                <div class='current_details'>
						                  <p class='current_text'>{$currentValue}</p>
						                  <a class='edit_payout_detail' href='#'>edit</a>
						                </div>
						                <div class='input_field'>
						                  <select class='payout_detail' data-log='{$row->log_id}' name='payout_type'>
						                    <option value='wise' ".($row->payout_type=='wise'?'selected':'').">Wise</option>
						                    <option value='paypal' ".($row->payout_type=='paypal'?'selected':'').">Paypal</option>
						                  </select>
						                  <a class='cancel_payout_detail' href='#'>cancel</a>
						                </div>
						              </div>";
						})->editColumn('payout_id', function($row){
								$val = $row->payout_id ?? "No Details";
								return "<div class='edit_details'>
							                <div class='current_details'>
							                  <p class='current_text'>{$val}</p>
							                  <a class='edit_payout_detail' href='#'>edit</a>
							                </div>
							                <div class='input_field'>
							                  <input name='payout_id' class='payout_detail' type='text' data-log='{$row->log_id}' value='{$row->payout_id}'>
							                  <a class='cancel_payout_detail' href='#'>cancel</a>
							                </div>
							              </div>";
						})->editColumn('country', function($row){
							$val = $row->country ?? "No Details";
							// Suppose you have config('countries') in a helper
							// We can’t easily access config() from here unless you do it statically
							// but you can do it if needed, or do a separate join.
							// For demonstration, we’ll replicate your structure:
							$countryHtml = "<div class='edit_details'>
			                <div class='current_details'>
			                  <p class='current_text'>{$val}</p>
			                  <a class='edit_payout_detail' href='#'>edit</a>
			                </div>
			                <div class='input_field'>
			                  <select class='payout_detail' data-log='{$row->log_id}' name='country'>
			                    <option value=''>Select Your Country</option>";
								$countries = config('countries', []);
								foreach($countries as $key => $country) {
									$selected = ($row->country==$key)?'selected':'';
									$countryHtml .= "<option value='{$key}' {$selected}>{$country['name']}</option>";
								}
								// Then close it:
								$countryHtml .= "</select>
				                  <a class='cancel_payout_detail' href='#'>cancel</a>
				                </div>
				              </div>";

							return $countryHtml;
						})->editColumn('status', function($row){
			                  // replicate your if statements:
			                  if ($row->status == 'rollover') {
				                  return $row->status;
			                  } elseif ($row->status == 'pending') {
				                  return "<span class='status_wrap'>{$row->status}
						                    <span class='btn_span'>
						                      <a data-status='{$row->status}' data-log='{$row->log_id}' class='payout_status_button btn value_span11 value_span2 value_span4' href='#'>MARK PAID</a>
						                    </span>
						                  </span>";
			                  } else {
				                  return $row->status;
			                  }
		                  })
		                  ->rawColumns(['user_name','payout_dates', 'revenue', 'payout_type','payout_id','country','status'])->make(true);
	}
}
