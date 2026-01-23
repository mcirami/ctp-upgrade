<?php

namespace App\Http\Controllers;

use App\Click;
use App\Exports\ClicksExport;
use App\Exports\OfferDataExport;
use App\Exports\AffDataExport;
use App\Http\Controllers\Report\ReportController;
use Illuminate\Http\Request;
use LeadMax\TrackYourStats\Report\Repositories\Employee\GodEmployeeRepository;
use LeadMax\TrackYourStats\Report\Repositories\Offer\GodOfferRepository;
use LeadMax\TrackYourStats\System\Session;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Traits\ClickTraits;
use PhpOffice\PhpSpreadsheet\Exception;

class ExportDataController extends ReportController
{
	use ClickTraits;

	/**
	 * @throws Exception
	 * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
	 */
	public function exportUsersClicks($userId) {

		$dates = self::getDates();

		// Replicate the query used for the view
		$reportCollection = Click::where('rep_idrep', '=', $userId)
		                         ->where('clicks.click_type', '!=', 2)
		                         ->whereBetween('clicks.first_timestamp', [$dates['startDate'], $dates['endDate']])
		                         ->leftJoin('click_vars', 'click_vars.click_id', '=', 'clicks.idclicks')
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
			                         'click_geo.ip as ip_address',
		                         )
		                         ->orderBy('paid', 'DESC')->get();
		$report = $this->formatResults($reportCollection);
		return Excel::download(new ClicksExport($report), 'clicks.xlsx');
	}

	/**
	 * @throws Exception
	 * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
	 */
	public function exportOfferData() {
		$dates = self::getDates();
		$repo = new GodOfferRepository(\DB::getPdo());
		$data = $repo->between($dates['startDate'], $dates['endDate']);

		return Excel::download(new OfferDataExport($data), 'offer-data.xlsx');
	}

	public function exportAffData() {
		$dates = self::getDates();
		$repository = new GodEmployeeRepository(\DB::getPdo());
		$repository->SHOW_AFF_TYPE = request()->query('role', 3);
		$data = $repository->between($dates['startDate'], $dates['endDate']);

		return Excel::download(new AffDataExport($data), 'Aff-data.xlsx');
	}
}
