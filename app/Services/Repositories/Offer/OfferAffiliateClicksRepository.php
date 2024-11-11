<?php


namespace App\Services\Repositories\Offer;


use App\Click;
use App\Conversion;
use App\Privilege;
use App\Services\Repositories\Repository;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

/**
 * Reporting repository for Offers clicks, organized by affiliates.
 * @package App\Services\Repositories\Offer
 */
class OfferAffiliateClicksRepository implements Repository
{
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
        if(\LeadMax\TrackYourStats\System\Session::userType() == Privilege::ROLE_ADMIN) {
            $report = $this->getOfferConversionsForAdmin($start, $end);
        } else if(\LeadMax\TrackYourStats\System\Session::userType() == Privilege::ROLE_MANAGER) {
            $report = $this->getOfferConversionsForManager($start, $end);
        } else if(\LeadMax\TrackYourStats\System\Session::userType() == Privilege::ROLE_AFFILIATE) {
            $report = $this->getOfferConversionsForAgent($start, $end);
        } else {
            $report = $this->getOfferConversionsForGod($start, $end);
        }

		/*return \DB::query()->select([
			'rep.idrep as user_id',
			'rep.user_name',
			'offer.idoffer as offer_id',
			'offer.offer_name',
			\DB::raw('COUNT(clicks.idclicks) as clicks'),
			\DB::raw('COUNT(conversions.click_id) as conversions')
		])->from('conversions')->whereBetween('timestamp', [$start, $end])
		                 ->leftJoin('clicks', 'conversions.click_id', 'clicks.idclicks')
		                 ->leftJoin('rep', 'rep.idrep', 'conversions.user_id')
		                 ->leftJoin('offer', 'clicks.offer_idoffer', 'offer.idoffer')
		                 ->groupBy('rep.user_name', 'rep.idrep', 'offer_id')
		                 ->orderBy('conversions', 'DESC');;*/

                         
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
        return \DB::query()->select([
            'rep.idrep as user_id',
            'rep.user_name',
            'offer.idoffer as offer_id',
            'offer.offer_name',
            \DB::raw('COUNT(clks.idclicks) as clicks'),
            \DB::raw('COUNT(cnvs.click_id) as conversions')
        ])->from('rep_has_offer as rho')
            ->join('rep', function ($jc) {
                /* @var $jc JoinClause */
                $jc->on('rep.idrep', 'rho.rep_idrep');
               /* $jc->where('rep.lft', '>', $this->user->lft);
                $jc->where('rep.rgt', '<', $this->user->rgt);*/
            })
            ->join('offer', 'rho.offer_idoffer', 'offer.idoffer')
            ->join('clicks as clks', function ($jc) use ($start, $end) {
                /* @var $q JoinClause */
                $jc->on('clks.rep_idrep', 'rho.rep_idrep')
                    ->on('clks.offer_idoffer', 'rho.offer_idoffer')
                    ->whereBetween('clks.first_timestamp', [$start, $end]);
            })
            ->leftJoin('conversions as cnvs', 'cnvs.click_id', 'clks.idclicks')
            ->where('offer.idoffer', $this->offerId)
            ->where('rho.rep_idrep', $this->user->idrep)
            ->groupBy('rep.user_name', 'rep.idrep', 'offer_id')
            ->orderBy('conversions', 'DESC');
    }

    public function getOfferConversionsForManager($start, $end) {
        return \DB::query()->select([
            'rep.idrep as user_id',
            'rep.user_name',
            'offer.idoffer as offer_id',
            'offer.offer_name',
            \DB::raw('COUNT(clks.idclicks) as clicks'),
            \DB::raw('COUNT(cnvs.click_id) as conversions')
        ])->from('rep_has_offer as rho')
            ->join('rep', function ($jc) {
                /* @var $jc JoinClause */
                $jc->on('rep.idrep', 'rho.rep_idrep')->where('rep.referrer_repid', $this->user->idrep);
               /* $jc->where('rep.lft', '>', $this->user->lft);
                $jc->where('rep.rgt', '<', $this->user->rgt);*/
            })
            ->join('offer', 'rho.offer_idoffer', 'offer.idoffer')
            ->join('clicks as clks', function ($jc) use ($start, $end) {
                /* @var $q JoinClause */
                $jc->on('clks.rep_idrep', 'rho.rep_idrep')
                    ->on('clks.offer_idoffer', 'rho.offer_idoffer')
                    ->whereBetween('clks.first_timestamp', [$start, $end]);
            })
            ->leftJoin('conversions as cnvs', 'cnvs.click_id', 'clks.idclicks')
            ->where('offer.idoffer', $this->offerId)
            ->groupBy('rep.user_name', 'rep.idrep', 'offer_id')
            ->orderBy('conversions', 'DESC');
    }

    public function getOfferConversionsForAdmin($start, $end) {

        $managers = User::where('referrer_repid', $this->user->idrep)->pluck('idrep')->toArray();

        return \DB::query()->select([
            'rep.idrep as user_id',
            'rep.user_name',
            'offer.idoffer as offer_id',
            'offer.offer_name',
            \DB::raw('COUNT(clks.idclicks) as clicks'),
            \DB::raw('COUNT(cnvs.click_id) as conversions')
        ])->from('rep_has_offer as rho')
            ->join('rep', function ($jc) use($managers)  {
                /* @var $jc JoinClause */
                $jc->on('rep.idrep', 'rho.rep_idrep')->whereIn('rep.referrer_repid', $managers);
               /* $jc->where('rep.lft', '>', $this->user->lft);
                $jc->where('rep.rgt', '<', $this->user->rgt);*/
            })
            ->join('offer', 'rho.offer_idoffer', 'offer.idoffer')
            ->join('clicks as clks', function ($jc) use ($start, $end) {
                /* @var $q JoinClause */
                $jc->on('clks.rep_idrep', 'rho.rep_idrep')
                    ->on('clks.offer_idoffer', 'rho.offer_idoffer')
                    ->whereBetween('clks.first_timestamp', [$start, $end]);
            })
            ->leftJoin('conversions as cnvs', 'cnvs.click_id', 'clks.idclicks')
            ->where('offer.idoffer', $this->offerId)
            ->groupBy('rep.user_name', 'rep.idrep', 'offer_id')
            ->orderBy('conversions', 'DESC');
    }

    public function getOfferConversionsForGod($start, $end) {
       /*  return \DB::query()->select([
            'rep.idrep as user_id',
            'rep.user_name',
            'offer.idoffer as offer_id',
            'offer.offer_name',
            \DB::raw('COUNT(clks.idclicks) as clicks'),
            \DB::raw('COUNT(cnvs.click_id) as conversions')
        ])->from('rep_has_offer as rho')
            ->join('rep', function ($jc) { */
                /* @var $jc JoinClause */
               /*  $jc->on('rep.idrep', 'rho.rep_idrep'); */
               /* $jc->where('rep.lft', '>', $this->user->lft);
                $jc->where('rep.rgt', '<', $this->user->rgt);*/
           /*  })
            ->join('offer', 'rho.offer_idoffer', 'offer.idoffer')
            ->join('clicks as clks', function ($jc) use ($start, $end) { */
                /* @var $q JoinClause */
               /*  $jc->on('clks.rep_idrep', 'rho.rep_idrep')
                    ->on('clks.offer_idoffer', 'rho.offer_idoffer')
                    ->whereBetween('clks.first_timestamp', [$start, $end]);
            })
            ->leftJoin('conversions as cnvs', 'cnvs.click_id', 'clks.idclicks')
            ->where('offer.idoffer', $this->offerId)
            ->groupBy('rep.user_name', 'rep.idrep', 'offer_id')
            ->orderBy('conversions', 'DESC'); */

        return DB::table('clicks')
        ->where('offer_idoffer', '=', $this->offerId)
        ->whereBetween('first_timestamp',[$start, $end])
        ->leftJoin('rep', 'idrep', '=', 'clicks.rep_idrep')
        ->leftJoin('conversions', 'conversions.click_id', '=', 'clicks.idclicks')
        ->leftJoin('offer', 'offer.idoffer', '=', 'clicks.offer_idoffer')
        ->select('rep.idrep as user_id', 'rep.user_name', 'offer.idoffer as offer_id', 'offer.offer_name', DB::raw('COUNT(clicks.idclicks) as clicks'),
        DB::raw('COUNT(conversions.click_id) as conversions'))->groupBy('rep.user_name', 'rep.idrep', 'offer_id')->orderBy('conversions', 'DESC');
    } 
}