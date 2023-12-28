<?php


namespace App\Services\Repositories\Offer;


use App\Click;
use App\Services\Repositories\Repository;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;

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
                $jc->where('rep.lft', '>', $this->user->lft);
                $jc->where('rep.rgt', '<', $this->user->rgt);
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
            ->orderBy('clicks', 'DESC');
    }

    /**
     * Fetch results with the given dates.
     * @param Carbon $start
     * @param Carbon $end
     * @return mixed
     */
    public function between(Carbon $start, Carbon $end)
    {
        return $this->query($start, $end)->get()->toArray();
    }
}