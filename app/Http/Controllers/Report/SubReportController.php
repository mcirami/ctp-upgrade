<?php

namespace App\Http\Controllers\Report;

use App\Click;
use App\Offer;
use App\User;
use App\Conversion;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use LeadMax\TrackYourStats\Report\Reporter;
use LeadMax\TrackYourStats\Report\Repositories\SubVarRepository;
use LeadMax\TrackYourStats\Report\Filters;
use App\Http\Traits\ClickTraits;
use LeadMax\TrackYourStats\Clicks\ClickGeo;

class SubReportController extends ReportController
{

	use ClickTraits;

    public function show()
    {
        $dates = self::getDates();

        $repo = new SubVarRepository(\DB::getPdo());

        $repo->setSubNumber(request()->query('sub', 1));


        $reporter = new Reporter($repo);

        $reporter
            ->addFilter(new Filters\Total(['clicks','unique','conversions']));
            //->addFilter(new Filters\EarningPerClick('unique', 'revenue'))
            //->addFilter(new Filters\DollarSign(['EPC', 'revenue', 'TOTAL', 'Total']));

        return view('report.sub', compact('reporter', 'dates'));
    }

	public function showSubConversions(Request $request): View|Application|Factory|\Illuminate\Contracts\Foundation\Application {

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
		$offerData = Offer::findOrFail($offer);
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
				DB::raw('COUNT(clicks.idclicks) as clicks'),
				DB::raw('SUM(clicks.click_type = 0) as unique_clicks')
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
				 'offerData',
				 'user'
				));
	}

	public function showSubIdClicksByOffer(User $user, Offer $offer) {
		$dates = self::getDates();
		$startDate = $dates['originalStart'];
		$endDate = $dates['originalEnd'];
		$dateSelect = request()->query('dateSelect');
		$subId = request()->query('subId');

	    $reportCollection = Click::where('rep_idrep', '=', $user->idrep)
					->where('clicks.offer_idoffer', '=', $offer->idoffer)
	                ->where('clicks.click_type', '!=', 2)
	                ->whereBetween('clicks.first_timestamp', [$dates['startDate'], $dates['endDate']])
	                ->leftJoin('click_vars', function($query) {
						$query->on('click_vars.click_id', '=', 'clicks.idclicks')
						->whereRaw('clicks.first_timestamp >= NOW() - INTERVAL 2 YEAR');
					})
					->where('click_vars.sub1', '=', $subId)
	                ->leftJoin('conversions', 'conversions.click_id', '=', 'clicks.idclicks')
	                ->leftJoin('offer', 'offer.idoffer', '=', 'clicks.offer_idoffer')
	                ->select(
						'clicks.idclicks',
						'clicks.first_timestamp as timestamp',
						'offer.offer_name',
						'conversions.timestamp as conversion_timestamp',
						'conversions.paid as paid',
						'click_vars.url',
						'click_vars.sub1 as subId',
						'clicks.referer',
						'clicks.ip_address  as ip_address',
						'clicks.offer_idoffer  as offer_id'
	                )
	                ->orderBy('paid', 'DESC')->paginate(100);

		$reportCollection->appends(['subId' => $subId]);
		$report = $this->formatResults($reportCollection);

		
        return view('report.clicks.subid', 
		compact(
			'report', 
			'user',
			'subId',
			'offer',
			'reportCollection', 
			'startDate', 
			'endDate', 
			'dateSelect'
		));
	}

	public function showSubIdConversionsInCountry(User $user, Offer $offer) {
		$dates = self::getDates();
		$startDate = $dates['originalStart'];
		$endDate = $dates['originalEnd'];
		$dateSelect = request()->query('dateSelect');
		$country = request()->query('country');
		$userId = $user->idrep;
		$offerId = $offer->idoffer;

		$ipAddresses = Click::where('rep_idrep', '=', $userId)
		->where('offer_idoffer', '=', $offerId)
		->where('clicks.click_type', '!=', 2)
		->whereBetween('first_timestamp', [$dates['startDate'], $dates['endDate']])
		->where(function ($query) {
			$query->whereNull('country_code')
			->orWhere('country_code', '');
		})
		->pluck('ip_address')
		->toArray();

		$matchingIPs = array_filter($ipAddresses, function ($ip) use ($country) {
			$geo = ClickGeo::findGeo($ip);
			return $geo['isoCode'] === $country;
		});

		$clicksSubquery = Click::where('rep_idrep', '=', $userId)
		->where('offer_idoffer', '=', $offerId)
		->where('clicks.click_type', '!=', 2)
		->whereBetween('first_timestamp', [$dates['startDate'], $dates['endDate']])
		->where(function ($query) use($matchingIPs, $country) {
			$query->where('country_code', '=', $country)
			->orWhere(function ($subQuery) use ($matchingIPs) {
				$subQuery->whereIn('ip_address', $matchingIPs);
			});
		})
		->leftJoin('click_vars', 'click_vars.click_id', '=', 'clicks.idclicks')
		->leftJoin('conversions', 'conversions.click_id', '=', 'clicks.idclicks')
		->where('click_vars.sub1', '!=', '')
		->select(
			'click_vars.sub1 as subId',
			DB::raw('COUNT(clicks.idclicks) as clicks'), 
			DB::raw('SUM(clicks.click_type = 0) as unique_clicks'))
		->groupBy('subId');

		$conversionsSubquery = Conversion::where('user_id', '=', $userId)
			->whereBetween('timestamp', [$dates['startDate'], $dates['endDate']])
			->leftJoin('clicks', 'clicks.idclicks', '=', 'conversions.click_id')
			->leftJoin('click_vars', 'click_vars.click_id', '=', 'clicks.idclicks')
			->where('click_vars.sub1', '!=', '')
			->where('clicks.offer_idoffer', '=', $offerId)
			->where(function ($query) use($matchingIPs, $country) {
				$query->where('clicks.country_code', '=', $country)
				->orWhere(function ($subQuery) use ($matchingIPs) {
					$subQuery->whereIn('clicks.ip_address', $matchingIPs);
				});
			})
			->select(
				'click_vars.sub1 as subId', 
				DB::raw('COUNT(conversions.id) as total_conversions'))
			->groupBy('click_vars.sub1')
			->orderBy('total_conversions');

			$reportCollection = DB::table(DB::raw("({$clicksSubquery->toSql()}) as clicks"))
			->mergeBindings($clicksSubquery->getQuery())
			->leftJoin(DB::raw("({$conversionsSubquery->toSql()}) as conversions"), 'clicks.subId', '=', 'conversions.subId')
			->mergeBindings($conversionsSubquery->getQuery())
			->select(
				'clicks.subId',
				DB::raw('SUM(clicks.clicks) as total_clicks'),
				DB::raw('SUM(clicks.unique_clicks) as unique_clicks'),
				DB::raw('SUM(COALESCE(conversions.total_conversions, 0)) as total_conversions'),
			)
			->groupBy('clicks.subId')
			->orderBy('total_conversions', 'DESC')->paginate(100);

		return view('report.conversions.affiliate-sub-in-country', 
		compact(
			'reportCollection',
			'user',
			'startDate', 
			'endDate', 
			'dateSelect',
			'offer',
			'country'
		));
	}

	public function showSubIdClicksByOfferInCountry(User $user, Offer $offer) {
		$dates = self::getDates();
		$startDate = $dates['originalStart'];
		$endDate = $dates['originalEnd'];
		$dateSelect = request()->query('dateSelect');
		$subId = request()->query('subid');
		$country = request()->query('country');
		$userId = $user->idrep;
		$offerId = $offer->idoffer;

		$ipAddresses = Click::where('rep_idrep', '=', $userId)
		->where('offer_idoffer', '=', $offerId)
		->where('clicks.click_type', '!=', 2)
		->whereBetween('first_timestamp', [$dates['startDate'], $dates['endDate']])
		->where(function ($query) {
			$query->whereNull('country_code')
			->orWhere('country_code', '');
		})
		->pluck('ip_address')
		->toArray();

		$matchingIPs = array_filter($ipAddresses, function ($ip) use ($country) {
			$geo = ClickGeo::findGeo($ip);
			return $geo['isoCode'] === $country;
		});

	    $reportCollection = Click::where('rep_idrep', '=', $user->idrep)
					->where('clicks.offer_idoffer', '=', $offer->idoffer)
	                ->where('clicks.click_type', '!=', 2)
	                ->whereBetween('clicks.first_timestamp', [$dates['startDate'], $dates['endDate']])
	                ->leftJoin('click_vars', 'click_vars.click_id', '=', 'clicks.idclicks')
					->where('click_vars.sub1', '=', $subId)
					->where(function ($query) use($matchingIPs, $country) {
						$query->where('country_code', '=', $country)
						->orWhere(function ($subQuery) use ($matchingIPs) {
							$subQuery->whereIn('ip_address', $matchingIPs);
						});
					})
	                ->leftJoin('conversions', 'conversions.click_id', '=', 'clicks.idclicks')
	                ->leftJoin('offer', 'offer.idoffer', '=', 'clicks.offer_idoffer')
	                ->select(
						'clicks.idclicks',
						'clicks.first_timestamp as timestamp',
						'offer.offer_name',
						'conversions.timestamp as conversion_timestamp',
						'conversions.paid as paid',
						'click_vars.url',
						'click_vars.sub1 as subId',
						'clicks.referer',
						'clicks.ip_address  as ip_address',
						'clicks.offer_idoffer  as offer_id'
	                )
	                ->orderBy('paid', 'DESC')->paginate(100);
	
		$reportCollection->appends(['country' => $country, 'subid' => $subId]);
		$report = $this->formatResults($reportCollection);

		//dd($report);
		return view('report.clicks.subid-in-country', 
		compact(
			'report', 
			'user',
			'subId',
			'offer',
			'reportCollection', 
			'startDate', 
			'endDate', 
			'dateSelect',
			'country'
		));
	}

	public function subIdOfferConverisonsByCountry(User $user, Offer $offer) {
		$dates = self::getDates();
		$startDate = $dates['originalStart'];
		$endDate = $dates['originalEnd'];
		$dateSelect = request()->query('dateSelect');
		$subId = request()->query('subid');
        $userId = $user->idrep;
		$offerId = $offer->idoffer;

		$clicksSubquery = Click::whereBetween('first_timestamp', [$dates['startDate'], $dates['endDate']])
		->where('rep_idrep', '=', $userId)
		->where('offer_idoffer', '=', $offerId)
		->where('clicks.click_type', '!=', 2)
		->leftJoin('click_vars', 'click_vars.click_id', '=', 'clicks.idclicks')
		->where('click_vars.sub1', '=', $subId)
		->select(
			'click_vars.sub1',
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
			->where('clicks.offer_idoffer', '=', $offerId)
			->leftJoin('click_vars', 'click_vars.click_id', '=', 'clicks.idclicks')
			->where('click_vars.sub1', '=', $subId)
			->select(
				'clicks.ip_address', 
				'clicks.country_code',
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

		return view('report.conversions.affiliate-subid-by-country', 
		compact(
			'reportCollection',
			'reports',
			'subId',
			'user', 
			'startDate', 
			'endDate', 
			'dateSelect', 
			'offer',
		));
	}
}
