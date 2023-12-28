<?php

namespace App\Services\Repositories\Offer;

use App\Click;
use App\Services\Repositories\Repository;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use LeadMax\TrackYourStats\Clicks\ClickGeo;

/**
 * Reporting Repository for an Offers clicks.
 * @package App\Services\Repositories\Offer
 */
class OfferClicksRepository implements Repository
{

    /**
     * @var
     */
    public $offerId;

    /**
     * @var bool
     */
    public $showFraudData;

    /**
     * @var User
     */
    public $user;

    /**
     * OfferClicksRepository constructor.
     * @param $offerId
     * @param User $user
     * @param bool $showFraudData
     */
    public function __construct($offerId, User $user, $showFraudData = false)
    {
        $this->offerId = $offerId;
        $this->user = $user;
        $this->showFraudData = $showFraudData;
    }

    /**
     * Return the Eloquent query builder.
     * @param Carbon $start
     * @param Carbon $end
     * @return Builder
     */
    public function query(Carbon $start, Carbon $end): Builder
    {
        $select = [];
        if ($this->showFraudData) {
            $select[] = 'clicks.idclicks as id';
        }
        $select = array_merge($select, [
            'clicks.first_timestamp as timestamp',
            'conversions.timestamp as conversion_timestamp',
            'conversions.paid as paid',
            'click_vars.url as query_string',
            'clicks.rep_idrep as affiliate_id',
            'clicks.offer_idoffer as offer_id',
            'click_geo.ip as ip_address'
        ]);
        return Click::select($select)
            ->leftJoin('click_vars', 'click_vars.click_id', 'clicks.idclicks')
            ->leftJoin('click_geo', 'click_geo.click_id', 'clicks.idclicks')
            ->leftJoin('conversions', 'conversions.click_id', 'clicks.idclicks')
            ->join('rep', 'rep.idrep', 'clicks.rep_idrep')
            ->where('offer_idoffer', $this->offerId)
            ->where('rep.lft', '>', $this->user->lft)
            ->where('rep.rgt', '<', $this->user->rgt)
            ->whereBetween('clicks.first_timestamp', [$start, $end]);
    }

    /**
     * Fetch results with the given dates, with additional result formatting.
     * @param Carbon $start
     * @param Carbon $end
     * @return mixed
     */
    public function between(Carbon $start, Carbon $end)
    {
        return $this->formatResults($this->query($start, $end)->get());
    }

    /**
     * Apply the default formatting for the Query results, used by between(..)
     * This is public so if the consumer wishes to modify the original query(..), they can and will be able to use
     * the default formatting.
     * @param Collection $results
     * @return Collection
     */
    public function formatResults(Collection $results)
    {
        // Apply Geo Information to rows based on Clicks IP
        $results->transform(function ($row) {
            $geo = ClickGeo::findGeo($row->ip_address);
            if ($this->showFraudData && !empty($geo)) {
                foreach ($geo as $key => $val) {
                    $row[$key] = $val;
                }
            } else {
                // If we aren't showing fraud data, only show isoCode and unset IP
                if (isset($geo['isoCode'])) {
                    $row->isoCode = $geo['isoCode'];
                }
                unset($row->ip_address);
            }
            return $row;
        });

        // Parse URL into Sub1-Sub-5, and merge into row
        $results->transform(function ($row) {
            parse_str($row->query_string, $subVariables);
            for ($i = 1; $i <= 5; $i++) {
                if (!isset($subVariables['sub' . $i])) {
                    $subVariables['sub' . $i] = '';
                }
                $row->{'sub' . $i} = $subVariables['sub' . $i];
            }

            return $row;
        });

        return $results;
    }

}