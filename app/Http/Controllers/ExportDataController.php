<?php

namespace App\Http\Controllers;

use App\Click;
use App\ClickGeoCache;
use App\Exports\ClicksExport;
use App\Exports\OfferDataExport;
use App\Exports\AffDataExport;
use App\Exports\CountryClicksExport;
use App\Http\Controllers\Report\ReportController;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Traits\ClickTraits;
use LeadMax\TrackYourStats\Clicks\ClickGeo;
use LeadMax\TrackYourStats\Report\Repositories\Employee\GodEmployeeRepository;
use LeadMax\TrackYourStats\Report\Repositories\Offer\GodOfferRepository;
use PhpOffice\PhpSpreadsheet\Exception;
use App\Services\ClickGeoCacheService;
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
			                         'clicks.ip_address as ip_address',
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

	/**
	 * @throws Exception
	 * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
	 */
	public function exportAffData() {
		$dates = self::getDates();
		$repository = new GodEmployeeRepository(\DB::getPdo());
		$repository->SHOW_AFF_TYPE = request()->query('role', 3);
		$data = $repository->between($dates['startDate'], $dates['endDate']);

		return Excel::download(new AffDataExport($data), 'Aff-data.xlsx');
	}

	/**
	 * @throws Exception
	 * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
	 */
	public function exportCountryClicks(ClickGeoCacheService $geoCache) {
		$dates = self::getDates();
		$geoCode = request()->query('country');

		$ips = Click::query()
		            ->whereBetween('first_timestamp', [$dates['startDate'], $dates['endDate']])
		            ->where('click_type', '!=', 2)
		            ->whereNull('country_code')              // clicks table doesn't have it stored
		            ->distinct()
		            ->pluck('ip_address');

		$geoCache->warm($ips);

		$report = Click::query()
		               ->leftJoin('click_vars', 'click_vars.click_id', '=', 'clicks.idclicks')
		               ->leftJoin('conversions', 'conversions.click_id', '=', 'clicks.idclicks')
		               ->leftJoin('click_geo_cache as geo', 'geo.ip_address', '=', 'clicks.ip_address')
		               ->join('rep', 'rep.idrep', '=', 'clicks.rep_idrep')
		               ->leftJoin('offer', 'offer.idoffer', '=', 'clicks.offer_idoffer')
		               ->whereBetween('first_timestamp', [$dates['startDate'], $dates['endDate']])
		               ->where('clicks.click_type', '!=', 2)
		               ->when($geoCode, function ($q) use ($geoCode) {
			               $q->whereRaw(
				               'COALESCE(clicks.country_code, geo.country_code) = ?',
				               [$geoCode]
			               );
		               })
		               ->select([
			               'clicks.idclicks',
			               'clicks.first_timestamp',
			               'conversions.timestamp as conversion_timestamp',
			               'conversions.paid',
			               'click_vars.sub1',
			               'click_vars.sub2',
			               'click_vars.sub3',
			               'clicks.rep_idrep',
			               'clicks.offer_idoffer',
			               'clicks.referer',
			               'geo.ip_address as click_geo_ip',
		               ])
		               ->orderByDesc('conversions.paid')->get();

		return Excel::download(new CountryClicksExport($report), 'country-clicks.xlsx');
	}
}
