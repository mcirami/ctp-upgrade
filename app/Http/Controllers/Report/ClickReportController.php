<?php

namespace App\Http\Controllers\Report;

use App\Offer;
use App\Privilege;
use App\Services\Repositories\Offer\OfferAffiliateClicksRepository;
use App\Services\Repositories\Offer\OfferClicksRepository;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use LeadMax\TrackYourStats\Clicks\ClickGeo;
use LeadMax\TrackYourStats\Clicks\ClickVars;
use function GuzzleHttp\Psr7\parse_query;
use phpDocumentor\Reflection\Types\Object_;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use LeadMax\TrackYourStats\Report\ID\Clicks;
use LeadMax\TrackYourStats\System\Session;
use LeadMax\TrackYourStats\Table\Paginate;
use LeadMax\TrackYourStats\User\Permissions;
use Illuminate\Contracts\Support\Jsonable;

class ClickReportController extends ReportController
{

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

	    $start = Carbon::parse( $dates['start'], 'America/New_York' );
	    $end   = Carbon::parse( $dates['end'], 'America/New_York' );

	    $repo          = new OfferClicksRepository( $id, Session::user(),
		    Session::permissions()->can( Permissions::VIEW_FRAUD_DATA ) );
	    $clickReport     = $repo->between( $start, $end );
	    $page            = request()->query( 'page', 1 );
	    $rpp             = request()->query( 'rpp', 10 );
	    $clickReport     = new LengthAwarePaginator( $clickReport->forPage( $page, $rpp ), $clickReport->count(),
		    $rpp, $page,
		    [ 'path' => request()->fullUrlWithQuery( request()->except( 'page' ) ) ] );

		if (request()->query('filter') == 'affiliate') {

			$affiliateRepo = new OfferAffiliateClicksRepository( $id, Session::user() );
			$affiliateReport = $affiliateRepo->between( $start, $end );

		} else {
			$affiliateReport = $this->showManagersClicks($id);
		}

        return view('report.clicks.offer', compact('offer', 'affiliateReport', 'clickReport'));
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

	    $reportData = DB::table('clicks')
	                ->where('rep_idrep', '=', $userId)
	                ->where('clicks.click_type', '!=', 2)
	                ->whereBetween('clicks.first_timestamp', [$dates['startDate'], $dates['endDate']])
	                ->leftJoin('click_vars', 'click_vars.click_id', '=', 'clicks.idclicks')
	                ->leftJoin('click_geo', 'click_geo.click_id', '=', 'clicks.idclicks')
	                ->leftJoin('conversions', 'conversions.click_id', '=', 'clicks.idclicks')
	                ->leftJoin('offer', 'offer.idoffer', '=', 'clicks.offer_idoffer')
	                ->select(
						'clicks.idclicks',
						'clicks.first_timestamp',
						'offer.offer_name',
						'conversions.timestamp',
						'conversions.paid',
						'click_vars.url',
						'click_vars.sub1',
						'click_vars.sub2',
		                'click_vars.sub3',
		                'click_vars.sub4',
		                'click_vars.sub5',
						'click_geo.ip',
						'clicks.offer_idoffer'
	                )
	                ->orderBy('clicks.idclicks', 'DESC')->paginate(100);

	    $per = Permissions::loadFromSession();
		$report = $reportData->items();

	    if ($per->can("view_fraud_data")) {
		    foreach ($report as $row => $val) {
			    $geo = ClickGeo::findGeo($val->ip);

			    foreach ($geo as $key => $val2) {
				    $val->$key = $val2;
			    }
		    }
	    } else {
		    foreach ($report as $row => $val) {
			    $geo = ClickGeo::findGeo($val->ip);
			    $val->isoCode = $geo["isoCode"];
			    unset($val->ip);
		    }
	    }

        return view('report.clicks.affiliate', compact('report', 'user', 'reportData', 'startDate', 'endDate', 'dateSelect'));
    }

	public function showManagersClicks($id) {

		$dates = self::getDates();
		$affClicks = [];
		$start = Carbon::parse($dates['start'], 'America/New_York');
		$end = Carbon::parse($dates['end'], 'America/New_York');

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

	public function searchClicks(Request $request, $userId) {

		$dates = self::getDates();

		$startDate = $dates['originalStart'];
		$endDate = $dates['originalEnd'];
		$dateSelect = request()->query('dateSelect');

		$user = User::myUsers()->findOrFail($userId);

		$reportData = DB::table('clicks')
		                ->where([
							['rep_idrep', '=', $userId],
			                [function ($query) use($request) {
								if ($s = $request->searchValue) {
									$query->orWhere('idclicks', 'LIKE', '%' . $s . '%');
								}
							}]])
		                    ->whereBetween('clicks.first_timestamp', [$dates['startDate'], $dates['endDate']])
		                    ->leftJoin('click_vars', 'click_vars.click_id', '=', 'clicks.idclicks')
		                    ->leftJoin('click_geo', 'click_geo.click_id', '=', 'clicks.idclicks')
		                    ->leftJoin('conversions', 'conversions.click_id', '=', 'clicks.idclicks')
		                    ->leftJoin('offer', 'offer.idoffer', '=', 'clicks.offer_idoffer')
		                    ->select(
								'clicks.idclicks',
								'clicks.first_timestamp',
								'offer.offer_name',
								'conversions.timestamp',
								'conversions.paid',
								'click_vars.url',
								'click_vars.sub1',
								'click_vars.sub2',
								'click_vars.sub3',
								'click_vars.sub4',
								'click_vars.sub5',
								'click_geo.ip',
								'clicks.offer_idoffer'
		                    )
		                    ->orderBy('clicks.idclicks', 'DESC')->paginate(100);

		$per = Permissions::loadFromSession();
		$report = $reportData->items();

		if ($per->can("view_fraud_data")) {
			foreach ($report as $row => $val) {
				$geo = ClickGeo::findGeo($val->ip);

				foreach ($geo as $key => $val2) {
					$val->$key = $val2;
				}
			}
		} else {
			foreach ($report as $row => $val) {
				$geo = ClickGeo::findGeo($val->ip);
				$val->isoCode = $geo["isoCode"];
				unset($val->ip);
			}
		}

		return view('report.clicks.affiliate', compact('report', 'user', 'reportData', 'startDate', 'endDate', 'dateSelect'));

	}

}
