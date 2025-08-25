<?php

namespace App\Http\Controllers\Report;

use App\Conversion;
use App\User;
use App\Click;
use App\Offer;
use App\Http\Traits\ClickTraits;
use Illuminate\Support\Facades\DB;
use LeadMax\TrackYourStats\Clicks\ClickGeo;

class ConversionReportController extends ReportController
{
    use ClickTraits;

    public function showUserConversions($userId) {
		$dates = self::getDates();
		$startDate = $dates['originalStart'];
		$endDate = $dates['originalEnd'];
		$dateSelect = request()->query('dateSelect');
		$offerId = request()->query('offer');

        $user = User::myUsers()->findOrFail($userId);

		$reportCollection = Conversion::where('user_id', '=', $userId)
	                ->whereBetween('timestamp', [$dates['startDate'], $dates['endDate']])
					->leftJoin('clicks', 'clicks.idclicks', '=', 'conversions.click_id')
	                ->leftJoin('click_vars', 'click_vars.click_id', '=', 'conversions.click_id')
	                ->leftJoin('offer', 'offer.idoffer', '=', 'clicks.offer_idoffer')
	                ->select(
						'clicks.idclicks',
						'offer.offer_name',
						'conversions.timestamp as conversion_timestamp',
						'conversions.paid as paid',
						'click_vars.sub1',
						'click_vars.sub2',
		                'click_vars.sub3',
		                'click_vars.sub4',
		                'click_vars.sub5',
	                )
					->where('clicks.offer_idoffer', '=', $offerId)
	                ->orderBy('conversions.timestamp', 'DESC')->paginate(100);

		$report = $this->formatResults($reportCollection);

        return view('report.conversions.affiliate', compact(
			'report', 
			'user', 
			'reportCollection',
			'startDate', 
			'endDate', 
			'dateSelect',
			'offerId'
		));
	}

    public function showUserConversionsByOffer($userId) {
		$dates = self::getDates();
		$startDate = $dates['originalStart'];
		$endDate = $dates['originalEnd'];
		$dateSelect = request()->query('dateSelect');

        $user = User::findOrFail($userId);

		$clicksSubquery = Click::where('rep_idrep', '=', $userId)
			->whereBetween('first_timestamp', [$dates['startDate'], $dates['endDate']])
			->select(
				'ip_address', 
				'first_timestamp', 
				'idclicks', 
				'offer_idoffer', 
				'click_type', 
				DB::raw('COUNT(idclicks) as clicks'),
				DB::raw('SUM(clicks.click_type = 0) as unique_clicks')
				)
			->groupBy('offer_idoffer');

		$conversionsSubquery = Conversion::where('user_id', '=', $userId)
			->whereBetween('timestamp', [$dates['startDate'], $dates['endDate']])
			->leftJoin('clicks', 'clicks.idclicks', '=', 'conversions.click_id')
			->leftJoin('offer', 'offer.idoffer', '=', 'clicks.offer_idoffer')
			->select('offer.idoffer', DB::raw('COUNT(conversions.id) as conversions'))
			->groupBy('offer.idoffer');

		$report = DB::table(DB::raw("({$clicksSubquery->toSql()}) as clicks"))
			->mergeBindings($clicksSubquery->getQuery())
			->leftJoin(DB::raw("({$conversionsSubquery->toSql()}) as conversions"), 'clicks.offer_idoffer', '=', 'conversions.idoffer')
			->mergeBindings($conversionsSubquery->getQuery())
			->leftJoin('offer', 'offer.idoffer', '=', 'clicks.offer_idoffer')
			->select(
				'offer.idoffer',
				'offer.offer_name',
				'clicks.clicks as total_clicks',
				'clicks.unique_clicks as unique_clicks',
				DB::raw('COALESCE(conversions.conversions, 0) as conversions'),
			)
			->orderBy('conversions', 'DESC')
			->paginate(100);

			return view('report.conversions.affiliate-by-offer', 
			compact(
				'report', 
				'user', 
				'startDate', 
				'endDate', 
				'dateSelect'
			));
	}

    public function showUserOfferConversionsByCountry(User $user, Offer $offer) {
		$dates = self::getDates();
		$startDate = $dates['originalStart'];
		$endDate = $dates['originalEnd'];
		$dateSelect = request()->query('dateSelect');
        $userId = $user->idrep;
		$offerId = $offer->idoffer;

		$clicksSubquery = Click::whereBetween('first_timestamp', [$dates['startDate'], $dates['endDate']])
		->where('rep_idrep', '=', $userId)
		->where('offer_idoffer', '=', $offerId)
		->where('clicks.click_type', '!=', 2)
		->select(
			'idclicks',
			'ip_address', 
			'country_code',
			'click_type',
			DB::raw('COUNT(idclicks) as clicks'),
			DB::raw('SUM(clicks.click_type = 0) as unique_clicks'))
		->groupBy('ip_address');

		$conversionsSubquery = Conversion::where('user_id', '=', $userId)
			->whereBetween('timestamp', [$dates['startDate'], $dates['endDate']])
			->leftJoin('clicks', 'clicks.idclicks', '=', 'conversions.click_id')
			->select(
				'clicks.ip_address', 
				'clicks.country_code' ,
				DB::raw('COUNT(conversions.id) as conversions'))
			->where('clicks.offer_idoffer', '=', $offerId)
			->groupBy('clicks.ip_address', 'clicks.country_code');

			$reportCollection = DB::table(DB::raw("({$clicksSubquery->toSql()}) as clicks"))
			->mergeBindings($clicksSubquery->getQuery())
			->leftJoin(DB::raw("({$conversionsSubquery->toSql()}) as conversions"), 'clicks.ip_address', '=', 'conversions.ip_address')
			->mergeBindings($conversionsSubquery->getQuery())
			->select(
				'clicks.ip_address',
				'clicks.country_code',
				DB::raw('SUM(clicks.clicks) as total_clicks'),
				DB::raw('SUM(clicks.unique_clicks) as unique_clicks'),
				DB::raw('SUM(COALESCE(conversions.conversions, 0)) as total_conversions'),
			)
			->groupBy('clicks.ip_address', 'clicks.country_code')
			->orderBy('total_conversions', 'DESC')->get();
		
		foreach($reportCollection as $item) {
			if (is_null($item->country_code)) {
				$geo = ClickGeo::findGeo($item->ip_address);
				$item->country_code = $geo['isoCode'];
			}
		}

		$reports = [];

		foreach ($reportCollection as $item) {
			$countryCode = $item->country_code;
			if (!isset($reports[$countryCode])) {
				$reports[$countryCode] = [
					'country_code' => $countryCode,
					'total_clicks' => 0,
					'unique_clicks' => 0,
					'total_conversions' => 0
				];
			}
			$reports[$countryCode]['total_clicks'] += $item->total_clicks;
			$reports[$countryCode]['unique_clicks'] += $item->unique_clicks;
			$reports[$countryCode]['total_conversions'] += $item->total_conversions;
		}

		return view('report.conversions.affiliate-by-country', 
		compact(
			'reportCollection',
			'reports',
			'user', 
			'startDate', 
			'endDate', 
			'dateSelect', 
			'offer',
		));
	}
}
