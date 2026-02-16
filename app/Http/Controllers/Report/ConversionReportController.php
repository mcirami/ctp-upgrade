<?php

namespace App\Http\Controllers\Report;

use App\ClickGeoCache;
use App\Conversion;
use App\User;
use App\Click;
use App\Offer;
use Carbon\Carbon;
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
				$cachedIP = ClickGeoCache::query()
				                         ->where('ip_address', $item->ip_address)
				                         ->pluck('country_code')
				                         ->first();
				if ($cachedIP) {
					$item->country_code = $cachedIP;
				} else {
					$geo = ClickGeo::findGeo($item->ip_address);
					$item->country_code = $geo['isoCode'];
				}
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

	public function showConversionsByCountry() {
		$dates = self::getDates();
		$startDate = $dates['originalStart'];
		$endDate = $dates['originalEnd'];
		$dateSelect = request()->query('dateSelect');

		$clicksSubquery = Click::whereBetween('first_timestamp', [$dates['startDate'], $dates['endDate']])
		                       ->where('clicks.click_type', '!=', 2)
		                       ->select(
			                       'idclicks',
			                       'ip_address',
			                       'country_code',
			                       'click_type',
			                       DB::raw('COUNT(idclicks) as clicks'),
			                       DB::raw('SUM(clicks.click_type = 0) as unique_clicks'))
		                       ->groupBy('ip_address');

		$conversionsSubquery = Conversion::whereBetween('timestamp', [$dates['startDate'], $dates['endDate']])
		                                 ->leftJoin('clicks', 'clicks.idclicks', '=', 'conversions.click_id')
		                                 ->select(
			                                 'clicks.ip_address',
			                                 'clicks.country_code' ,
			                                 DB::raw('COUNT(conversions.id) as conversions'))
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

		return view('report.conversions.geo',
			compact(
				'reports',
				'startDate',
				'endDate',
				'dateSelect',
			));
	}

	public function showGeoByOffer() {
		$dates = self::getDates();
		$startDate = $dates['originalStart'];
		$endDate = $dates['originalEnd'];
		$dateSelect = request()->query('dateSelect');
		$geoCode = request()->query('country');

		$ipsMissingGeo = Click::query()
		                      ->whereBetween('first_timestamp', [$dates['startDate'], $dates['endDate']])
		                      ->where('click_type', '!=', 2)
		                      ->whereNull('country_code')              // clicks table doesn't have it stored
		                      ->distinct()
		                      ->pluck('ip_address');

		if (count($ipsMissingGeo) > 0) {
			foreach ( $ipsMissingGeo as $ip ) {
				$geo = ClickGeo::findGeo( $ip ); // your existing lookup
				if ( ! empty( $geo['isoCode'] ) ) {
					// Upsert into a local cache table keyed by ip
					ClickGeoCache::updateOrCreate(
						[ 'ip_address' => $ip ],
						[ 'country_code' => $geo['isoCode'] ]
					);
				}
			}
		}

		$clicksByOffer = Click::query()
		                      ->whereBetween('first_timestamp', [$dates['startDate'], $dates['endDate']])
		                      ->where('clicks.click_type', '!=', 2)
		                      ->leftJoin('click_geo_cache as geo', 'geo.ip_address', '=', 'clicks.ip_address')
		                      ->leftJoin('offer', 'offer.idoffer', '=', 'clicks.offer_idoffer')
		                      ->selectRaw('
								offer.offer_name,
						        clicks.offer_idoffer AS offer_id,
						        COALESCE(clicks.country_code, geo.country_code) AS country_code,
						        COUNT(*) AS total_clicks,
						        SUM(clicks.click_type = 0) AS unique_clicks
						    ')
		                      ->when($geoCode, fn ($q) => $q->whereRaw('COALESCE(clicks.country_code, geo.country_code) = ?', [$geoCode]))
		                      ->groupBy('clicks.offer_idoffer', DB::raw('COALESCE(clicks.country_code, geo.country_code)'));

		$conversionsByOffer = Conversion::query()
		                                ->whereBetween('conversions.timestamp', [$dates['startDate'], $dates['endDate']])
		                                ->join('clicks', 'clicks.idclicks', '=', 'conversions.click_id')
		                                ->leftJoin('click_geo_cache as geo', 'geo.ip_address', '=', 'clicks.ip_address')
		                                ->leftJoin('offer', 'offer.idoffer', '=', 'clicks.offer_idoffer')
		                                ->selectRaw('
                                    offer.offer_name,
							        clicks.offer_idoffer AS offer_id,
							        COALESCE(clicks.country_code, geo.country_code) AS country_code,
							        COUNT(conversions.id) AS total_conversions
							    ')
		                                ->when($geoCode, fn ($q) => $q->whereRaw('COALESCE(clicks.country_code, geo.country_code) = ?', [$geoCode]))
		                                ->groupBy('clicks.offer_idoffer',
			                                DB::raw('COALESCE(clicks.country_code, geo.country_code)'
			                                ));


		$report = DB::query()
		            ->fromSub($clicksByOffer, 'c')
		            ->leftJoinSub($conversionsByOffer, 'v', function ($join) {
			            $join->on('c.offer_id', '=', 'v.offer_id')
			                 ->on('c.country_code', '=', 'v.country_code');
		            })
		            ->selectRaw('
		        c.offer_name,
		        c.offer_id,
		        c.country_code,
		        c.total_clicks,
		        c.unique_clicks,
		        COALESCE(v.total_conversions, 0) AS total_conversions
		    ')
		            ->orderByDesc('total_conversions')
		            ->get();

		$totals = [
			'total_clicks' => (int) $report->sum('total_clicks'),
			'unique_clicks' => (int) $report->sum('unique_clicks'),
			'total_conversions' => (int) $report->sum('total_conversions'),
		];

		return view('report.conversions.offer-geo',
			compact(
				'geoCode',
				'report',
				'totals',
				'startDate',
				'endDate',
				'dateSelect',
			));

	}

	public function showManagerConversionsByOffer(User $user) {

		$dates = self::getDates();
		$startDate = $dates['originalStart'];
		$endDate = $dates['originalEnd'];
		$dateSelect = request()->query('dateSelect');
		$managerId = $user->idrep;

		$clicksSubquery = Click::whereBetween('first_timestamp', [$dates['startDate'], $dates['endDate']])
		                       ->whereIn('rep_idrep', function ($query) use ($managerId) {
			                       $query->select('idrep')->from('rep')->where('referrer_repid', '=', $managerId);
		                       })
		                       ->where('clicks.click_type', '!=', Click::TYPE_BLACKLISTED)
		                       ->select(
			                       'offer_idoffer',
			                       DB::raw('COUNT(idclicks) as clicks'),
			                       DB::raw('SUM(clicks.click_type = ' . Click::TYPE_UNIQUE . ') as unique_clicks')
		                       )
		                       ->groupBy('offer_idoffer');

		$conversionsSubquery = Conversion::whereBetween('timestamp', [$dates['startDate'], $dates['endDate']])
		                                 ->whereIn('user_id', function ($query) use ($managerId) {
			                                 $query->select('idrep')->from('rep')
			                                       ->where('rep.referrer_repid', '=', $managerId);
		                                 })
		                                 ->join('clicks', 'clicks.idclicks', '=', 'conversions.click_id')
		                                 ->select(
			                                 'clicks.offer_idoffer',
			                                 DB::raw('COUNT(conversions.id) as conversions'))
		                                 ->groupBy('clicks.offer_idoffer');

		$report = DB::table(DB::raw("({$clicksSubquery->toSql()}) as clicks"))
		            ->mergeBindings($clicksSubquery->getQuery())
		            ->join(DB::raw("({$conversionsSubquery->toSql()}) as conversions"), 'clicks.offer_idoffer', '=', 'conversions.offer_idoffer')
		            ->mergeBindings($conversionsSubquery->getQuery())
		            ->leftJoin('offer', 'offer.idoffer', '=', 'clicks.offer_idoffer')
		            ->select(
			            'offer.idoffer',
			            'offer.offer_name',
			            'clicks.clicks as total_clicks',
			            'clicks.unique_clicks as unique_clicks',
			            DB::raw('COALESCE(conversions.conversions, 0) as conversions')
		            )
		            ->orderBy('conversions', 'DESC')
		            ->paginate(100);

		return view('report.conversions.affiliate-by-offer',
			compact(
				'user',
				'report',
				'startDate',
				'endDate',
				'dateSelect'
			));
	}
}
