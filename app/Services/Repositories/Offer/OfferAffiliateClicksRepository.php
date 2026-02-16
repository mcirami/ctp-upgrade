<?php


namespace App\Services\Repositories\Offer;


use App\Click;
use App\Conversion;
use App\Privilege;
use App\Services\Repositories\Repository;
use App\User;
use App\ClickGeoCache;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use LeadMax\TrackYourStats\Clicks\ClickGeo;
use LeadMax\TrackYourStats\System\Session;
use App\Http\Traits\ReportsByCountryTrait;

/**
 * Reporting repository for Offers clicks, organized by affiliates.
 * @package App\Services\Repositories\Offer
 */
class OfferAffiliateClicksRepository implements Repository
{

	use ReportsByCountryTrait;
    /**
     * @var
     */
    private $offerId;

    /**
     * @var User
     */
    private $user;

    /**
     * OfferAffiliateClicksRepository constructor.
     * @param $offerId
     * @param User $user
     */
    public function __construct($offerId, User $user)
    {
        $this->offerId = $offerId;
        $this->user = $user;
    }

    /**
     * Return the Eloquent query builder.
     * @param Carbon $start
     * @param Carbon $end
     * @return Builder
     */
    public function query(Carbon $start, Carbon $end): Builder
    {
        if( Session::userType() == Privilege::ROLE_ADMIN) {
            $report = Session::permissions()->can('view_all_users') ?
	            $this->getOfferConversionsForGod($start, $end)
	            :
	            $this->getOfferConversionsForAdmin($start, $end);

        } else if( Session::userType() == Privilege::ROLE_MANAGER) {
            $report = $this->getOfferConversionsForManager($start, $end);
        } else if( Session::userType() == Privilege::ROLE_AFFILIATE) {
            $report = $this->getOfferConversionsForAgent($start, $end);
        } else {
            $report = $this->getOfferConversionsForGod($start, $end);
        }
                                    
        return $report;
    }

    /**
     * Fetch results with the given dates.
     * @param Carbon $start
     * @param Carbon $end
     * @return mixed
     */
    public function between(Carbon $start, Carbon $end)
    {
        return $this->query($start, $end)->get();
    }

    public function getOfferConversionsForAgent($start, $end) {
        return DB::table('clicks')
        ->where('offer_idoffer', '=', $this->offerId)
        ->where('rep_idrep', '=', $this->user->idrep)
        ->whereBetween('first_timestamp',[$start, $end])
        ->join('rep', 'idrep', '=', 'clicks.rep_idrep')
        ->rightJoin('conversions', 'conversions.click_id', '=', 'clicks.idclicks')
        ->leftJoin('offer', 'offer.idoffer', '=', 'clicks.offer_idoffer')
        ->select('rep.idrep as user_id', 'rep.user_name', 'offer.idoffer as offer_id', 'offer.offer_name', 
        DB::raw('COUNT(clicks.idclicks) as clicks'),
        DB::raw('SUM(clicks.click_type = 0) as unique_clicks'),
        DB::raw('COUNT(conversions.click_id) as conversions'))
        ->groupBy('rep.user_name', 'rep.idrep', 'offer_id')->orderBy('conversions', 'DESC');
    }

    public function getOfferConversionsForManager($start, $end) {

        return DB::table('clicks')
        ->where('offer_idoffer', '=', $this->offerId)
        ->whereBetween('first_timestamp',[$start, $end])
        ->join('rep', function($query) {
            $query->on('idrep', '=', 'clicks.rep_idrep')
            ->where('rep.referrer_repid', $this->user->idrep);
        })
        ->rightJoin('conversions', 'conversions.click_id', '=', 'clicks.idclicks')
        ->leftJoin('offer', 'offer.idoffer', '=', 'clicks.offer_idoffer')
        ->select('rep.idrep as user_id', 'rep.user_name', 'offer.idoffer as offer_id', 'offer.offer_name', 
        DB::raw('COUNT(clicks.idclicks) as clicks'),
        DB::raw('SUM(clicks.click_type = 0) as unique_clicks'),
        DB::raw('COUNT(conversions.click_id) as conversions'))
        ->groupBy('rep.user_name', 'rep.idrep', 'offer_id')->orderBy('conversions', 'DESC');
    }

    public function getOfferConversionsForAdmin($start, $end) {

        $managers = User::where('referrer_repid', $this->user->idrep)->pluck('idrep')->toArray();

	    $clicksSubquery = Click::query()
	                           ->where('clicks.offer_idoffer', '=', $this->offerId)
	                           ->whereBetween('clicks.first_timestamp',[$start, $end])
	                           ->where('clicks.click_type', '!=', 2)
	                           ->join('rep', function($query) use($managers) {
								   $query->on('idrep', '=', 'clicks.rep_idrep')
								         ->whereIn('rep.referrer_repid', $managers);
							   })
	                           ->leftJoin('offer', 'offer.idoffer', '=', 'clicks.offer_idoffer')
	                           ->selectRaw('rep.idrep AS user_id,rep.user_name, offer.idoffer AS offer_id, offer.offer_name,
		                           COUNT(clicks.idclicks) AS clicks,
		                           SUM(clicks.click_type = 0) AS unique_clicks')
	                           ->groupBy('rep.user_name', 'rep.idrep', 'offer_id');

	    $conversionsSubquery = Conversion::query()
	                                     ->whereBetween('conversions.timestamp', [$start, $end])
	                                     ->join('clicks', 'conversions.click_id', '=', 'clicks.idclicks') // Join instead of whereIn
	                                     ->where('clicks.offer_idoffer', '=', $this->offerId)
	                                     ->join('rep', function($query) use($managers) {
										    $query->on('rep.idrep', '=', 'clicks.rep_idrep')
										          ->whereIn('rep.referrer_repid', $managers);
										 })
	                                     ->selectRaw('clicks.rep_idrep AS user_id, clicks.offer_idoffer AS offer_id, 
	                                     COUNT(conversions.id) AS conversions')
	                                     ->groupBy('clicks.rep_idrep', 'clicks.offer_idoffer');;

	    return DB::query()  // neutral builder
	              ->fromSub($clicksSubquery, 'clicks')
	              ->leftJoinSub($conversionsSubquery, 'conversions', function ($join) {
		              $join->on('clicks.offer_id', '=', 'conversions.offer_id')
		                   ->on('clicks.user_id', '=', 'conversions.user_id');
	              })
	              ->select([
		              'clicks.user_id',
		              'clicks.user_name',
		              'clicks.clicks',
		              'clicks.unique_clicks',
		              DB::raw('COALESCE(conversions.conversions, 0) AS conversions'),
	              ])
	              ->orderByDesc('conversions');
    }

    public function getOfferConversionsForGod($start, $end) {

	    $clicksSubquery = Click::where('offer_idoffer', '=', $this->offerId)
	                           ->whereBetween('first_timestamp',[$start, $end])
	                           ->where('clicks.click_type', '!=', 2)
	                           ->join('rep', 'idrep', '=', 'clicks.rep_idrep')
	                           ->leftJoin('offer', 'offer.idoffer', '=', 'clicks.offer_idoffer')
	                           ->groupBy('rep.user_name', 'rep.idrep', 'offer.idoffer')
	                           ->select(
		                           'rep.idrep as user_id',
		                           'rep.user_name',
		                           'offer.idoffer as offer_id',
		                           'offer.offer_name',
		                           DB::raw('COUNT(clicks.idclicks) as clicks'),
		                           DB::raw('SUM(clicks.click_type = 0) as unique_clicks')
	                           );

	    $conversionsSubquery = Conversion::whereBetween('timestamp', [$start, $end])
	                                     ->join('clicks', 'conversions.click_id', '=', 'clicks.idclicks') // Join instead of whereIn
	                                     ->where('clicks.offer_idoffer', '=', $this->offerId)
	                                     ->groupBy('clicks.offer_idoffer', 'clicks.rep_idrep')
	                                     ->select('clicks.offer_idoffer as conv_offer_id',
		                                     'clicks.rep_idrep as user_id',
		                                     DB::raw('COUNT(conversions.id) as conversions')
	                                     );

	    return DB::table(DB::raw("({$clicksSubquery->toSql()}) as clicks"))
	             ->mergeBindings($clicksSubquery->getQuery())
	             ->leftJoin(DB::raw("({$conversionsSubquery->toSql()}) as conversions"), function ($join) {
		             $join->on('clicks.offer_id', '=', 'conversions.conv_offer_id') // Match offer_id
		                  ->on('clicks.user_id', '=', 'conversions.user_id'); // Match user_id
	             }
	             )
	             ->mergeBindings($conversionsSubquery->getQuery())
	             ->select(
		             'clicks.user_id',
		             'clicks.user_name',
		             'clicks.clicks as clicks',
		             'clicks.unique_clicks as unique_clicks',
		             DB::raw('COALESCE(conversions.conversions, 0) as conversions')
	             )
	             ->orderBy('conversions', 'DESC');
    }

    public function getOfferConversionsByCountry($start, $end): array {
	    $clicksSubquery = Click::whereBetween('first_timestamp', [$start, $end])
	                           ->where('offer_idoffer', '=', $this->offerId)
	                           ->where('clicks.click_type', '!=', 2)
	                           ->leftJoin('click_vars', 'click_vars.click_id', '=', 'clicks.idclicks')
	                           ->select(
		                           'idclicks',
		                           'ip_address',
		                           'country_code',
		                           'click_type',
		                           DB::raw('COUNT(idclicks) as clicks'),
		                           DB::raw('SUM(clicks.click_type = 0) as unique_clicks'))
	                           ->groupBy('ip_address', 'clicks.country_code');

	    $conversionsSubquery = Conversion::whereBetween('timestamp', [$start, $end])
	                                     ->leftJoin('clicks', 'clicks.idclicks', '=', 'conversions.click_id')
	                                     ->where('clicks.offer_idoffer', '=', $this->offerId)
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
			    $geo = ClickGeoCache::query()
			                        ->where('ip_address', $item->ip_address)->first();
			    if($geo) {
				    $item->country_code = $geo->country_code;
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

	    return $reports;
    }
}