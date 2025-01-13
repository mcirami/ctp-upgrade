<?php

namespace App\Http\Controllers\Report;

use App\Click;
use App\Conversion;
use App\Exports\DataExport;
use App\Offer;
use App\Privilege;
use App\Services\Repositories\Offer\OfferAffiliateClicksRepository;
use App\Services\Repositories\Offer\OfferClicksRepository;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use LeadMax\TrackYourStats\System\Session;
use LeadMax\TrackYourStats\User\Permissions;
use App\Http\Traits\ClickTraits;
use LeadMax\TrackYourStats\Clicks\ClickGeo;
use Maatwebsite\Excel\Facades\Excel;

class ClickReportController extends ReportController
{

	use ClickTraits;

    /**
     * Shows an offers clicks, and affiliates with those clicks.
     * Shows only affiliates assigned to the current logged in user
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function offerClicks($id)
    {
        $offer = Offer::findOrFail($id);

        $dates = self::getDates();

	    $startDate = $dates['originalStart'];
	    $endDate = $dates['originalEnd'];
	    $dateSelect = request()->query('dateSelect');

	    $start = Carbon::parse( $dates['startDate'], 'America/New_York' );
	    $end   = Carbon::parse( $dates['endDate'], 'America/New_York' );

	    $repo          = new OfferClicksRepository( $id, Session::user(),
		    Session::permissions()->can( Permissions::VIEW_FRAUD_DATA ) );
	    $reportCollection      = $repo->between( $start, $end );
		$report                = $reportCollection->items();

        return view('report.clicks.offer', 
		compact(
			'offer', 
			'report', 
			'reportCollection', 
			'id', 
			'startDate', 
			'endDate', 
			'dateSelect'
		));
    } 

    public function showOfferClicks($id)
    {
        $dates = self::getDates();
        $offer = Offer::findOrFail($id);

        // TODO: This should REALLY be refactored
        $myAssignments = array(
            'd_from' => Carbon::today()->format('Y-m-d'),
            'd_to' => Carbon::today()->format('Y-m-d'),
            'dateSelect' => 0,

            'rpp' => 10,


            'idoffer' => $id,
        );


        $assign = new \LeadMax\TrackYourStats\Table\Assignments($myAssignments);

        $assign->getAssignments();
        $report = new \LeadMax\TrackYourStats\Report\ID\Offer($assign);

        $report->fetchReport($dates['startDate'], $dates['endDate']);

        /*$paginate = new Paginate(request()->query('rpp', 10),
            $report->getCount($dates['startDate'], $dates['endDate']));*/


        return view('report.clicks.offer', compact('report', 'offer', 'dates'));
    }

    public function showUsersClicks($userId)
    {
        $dates = self::getDates();
		$startDate = $dates['originalStart'];
		$endDate = $dates['originalEnd'];
		$dateSelect = request()->query('dateSelect');

        $user = User::myUsers()->findOrFail($userId);

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
						'click_geo.ip  as ip_address',
						'clicks.offer_idoffer  as offer_id'
	                )
	                ->orderBy('paid', 'DESC')->paginate(100);


		$report = $this->formatResults($reportCollection);

        return view('report.clicks.affiliate', 
		compact(
			'report', 
			'user', 
			'reportCollection', 
			'startDate', 
			'endDate', 
			'dateSelect'
		));
    }

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

        $user = User::myUsers()->findOrFail($userId);

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

	public function showUserConversionsByCountry($userId) {
		$dates = self::getDates();
		$startDate = $dates['originalStart'];
		$endDate = $dates['originalEnd'];
		$dateSelect = request()->query('dateSelect');
		$offerId = request()->query('offer');
        $user = User::myUsers()->findOrFail($userId);

		dd($dates['startDate']);

		$clicksSubquery = Click::whereBetween('first_timestamp', [$dates['startDate'], $dates['endDate']])
		->where('rep_idrep', '=', $userId)
		->where('offer_idoffer', '=', $offerId)
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
			'reports', 
			'user', 
			'startDate', 
			'endDate', 
			'dateSelect', 
			'offerId'
		));
	}

	public function showConversionsByUser($offerId) {

		$dates = self::getDates();
		$offer = Offer::findOrFail($offerId);

		/*$startDate = $dates['originalStart'];
		$endDate = $dates['originalEnd'];
		$dateSelect = request()->query('dateSelect');*/

		$start = Carbon::parse( $dates['startDate'], 'America/New_York' );
		$end   = Carbon::parse( $dates['endDate'], 'America/New_York' );

		$affiliateRepo = new OfferAffiliateClicksRepository( $offerId, Session::user() );
		$affiliateReport = $affiliateRepo->between( $start, $end );

		//dd($affiliateReport);
		/*
		 * if filter is managers
		 *  $affiliateReport = $this->showManagersClicks($id);
		*/

		return view('report.offer.conversions', compact('affiliateReport', 'offer'));
	}

	public function showManagersClicks($id) {

		$dates = self::getDates();
		$affClicks = [];
		$start = Carbon::parse($dates['startDate'], 'America/New_York');
		$end = Carbon::parse($dates['endDate'], 'America/New_York');

		$managers = User::myUsers()->withRole(Privilege::ROLE_MANAGER)->get();
		foreach ($managers as $manager) {

			$data = DB::table('clicks')
			          ->join('rep', function($join) use ($manager){
						  $join->on('clicks.rep_idrep', '=', 'rep.idrep');
						  $join->where('rep.referrer_repid', '=', $manager->idrep);
					  })
			          ->leftJoin('conversions', 'conversions.click_id', 'clicks.idclicks' )
			          ->where('offer_idoffer', '=', $id)
			          ->whereBetween('first_timestamp', array($start, $end))
			          ->select([
						  \DB::raw('COUNT(clicks.rep_idrep) as clicks'),
				          \DB::raw('COUNT(conversions.click_id) as conversions')
			          ])
			          ->get();

			$object = [
				'user_id' => $manager->idrep,
				'user_name' => $manager->user_name,
				'clicks' =>  $data[0]->clicks,
				'conversions' => $data[0]->conversions
			];
			array_push($affClicks, (object) $object);
		}

		return $affClicks;

	}

	public function searchClicks(Request $request, $id) {

		$dates = self::getDates();

		$startDate = $dates['originalStart'];
		$endDate = $dates['originalEnd'];
		$dateSelect = request()->query('dateSelect');
		$searchType = request()->query('searchType');
		$user = null;
		$offer = null;

		if ($searchType == "user") {

			$user = User::myUsers()->findOrFail( $id );
			$reportCollection = Click::where([
				[ 'rep_idrep', '=', $id ],
				[function ( $query ) use ( $request ) {
						if ( $s = $request->searchValue ) {
							$query->orWhere( 'idclicks', 'LIKE', '%' . $s . '%' );
						}
					}
				]])->whereBetween( 'clicks.first_timestamp', [ $dates['startDate'], $dates['endDate'] ] )
			       ->leftJoin( 'click_vars', 'click_vars.click_id', '=', 'clicks.idclicks' )
			       ->leftJoin( 'click_geo', 'click_geo.click_id', '=', 'clicks.idclicks' )
			       ->leftJoin( 'conversions', 'conversions.click_id', '=', 'clicks.idclicks' )
			       ->leftJoin( 'offer', 'offer.idoffer', '=', 'clicks.offer_idoffer' )
			       ->select(
					   'clicks.idclicks',
				       'clicks.first_timestamp as timestamp',
				       'offer.offer_name',
				       'conversions.timestamp  as conversion_timestamp',
				       'conversions.paid as paid',
				       'click_vars.url',
				       'click_vars.sub1',
				       'click_vars.sub2',
				       'click_vars.sub3',
				       'click_vars.sub4',
				       'click_vars.sub5',
				       'click_geo.ip as ip_address',
				       'clicks.offer_idoffer  as offer_id'
			       )
			       ->orderBy( 'conversions.paid', 'DESC' )->paginate( 100 );
		} else {

			$offer = Offer::findOrFail($id);
			$reportCollection = Click::where([
				[ 'offer_idoffer', '=', $id ],
				[function ( $query ) use ( $request ) {
					if ( $s = $request->searchValue ) {
						$query->orWhere( 'idclicks', 'LIKE', '%' . $s . '%' );
					}
				}]])->whereBetween( 'clicks.first_timestamp', [ $dates['startDate'], $dates['endDate'] ] )
			        ->leftJoin( 'click_vars', 'click_vars.click_id', '=', 'clicks.idclicks' )
			        ->leftJoin( 'click_geo', 'click_geo.click_id', '=', 'clicks.idclicks' )
			        ->leftJoin( 'conversions', 'conversions.click_id', '=', 'clicks.idclicks' )
			        ->leftJoin( 'offer', 'offer.idoffer', '=', 'clicks.offer_idoffer' )
			        ->select(
						'clicks.idclicks as id',
				        'clicks.first_timestamp as timestamp',
				        'clicks.rep_idrep as affiliate_id',
				        'conversions.timestamp as conversion_timestamp',
				        'conversions.paid as paid',
				        'click_vars.sub1',
				        'click_vars.sub2',
				        'click_vars.sub3',
				        'click_vars.sub4',
				        'click_vars.sub5',
				        'click_geo.ip as ip_address',
				        'clicks.offer_idoffer as offer_id',
				        'offer.offer_name',
			        )
			        ->orderBy( 'conversions.paid', 'DESC' )->paginate( 100 );
		}


		$report = $this->formatResults($reportCollection);

		if ($searchType == "user") {
			return view('report.clicks.affiliate', compact('user', 'report', 'reportCollection', 'startDate', 'endDate', 'dateSelect'));
		} else {
			return view('report.clicks.offer', compact('offer', 'report', 'reportCollection', 'startDate', 'endDate', 'dateSelect'));
		}
	}

	public function exportUsersClicks($userId) {

		$dates = self::getDates();
		$startDate = $dates['originalStart'];
		$endDate = $dates['originalEnd'];

		$query = Click::query()
		->whereBetween('first_timestamp', [ $startDate, $endDate])
		->where('rep_idrep', $userId);
		$data = $query->get();
		return Excel::download(new DataExport($data), 'clicks.xlsx');
	}
}
