<?php

namespace App\Http\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Click;
use App\Conversion;
use LaravelIdea\Helper\App\_IH_Click_QB;
use LaravelIdea\Helper\App\_IH_Conversion_QB;
use LeadMax\TrackYourStats\Clicks\ClickGeo;

trait ReportsByCountryTrait
{
	/**
	 * Public entry point replacing both getOfferConversionsByCountry() and getAllConversionsByCountry().
	 * If $offerId is null -> all offers in range. If set -> filtered to that offer only.
	 */
	public function getConversionsByCountry($start, $end, ?int $offerId = null): array
	{
		$clicksSub = $this->buildClicksSubquery($start, $end, $offerId);
		$convSub   = $this->buildConversionsSubquery($start, $end, $offerId);

		// Compose using fromSub/joinSub to preserve bindings
		$rows = DB::query()
		          ->fromSub($clicksSub, 'clicks')
		          ->leftJoinSub($convSub, 'conversions', function ($join) {
			          $join->on('clicks.ip_address', '=', 'conversions.ip_address');
			          // NULL-safe equality so NULL matches NULL
			          $join->whereRaw('clicks.country_code <=> conversions.country_code');
		          })
		          ->selectRaw('
	                clicks.ip_address,
	                clicks.country_code,
	                SUM(clicks.clicks) AS total_clicks,
	                SUM(clicks.unique_clicks) AS unique_clicks,
	                COALESCE(SUM(conversions.conversions), 0) AS total_conversions'
		          )
		          ->groupBy('clicks.ip_address', 'clicks.country_code')
		          ->orderByDesc('total_conversions')
		          ->get();

		// Fill missing country codes via geo lookup
		$rows->transform(function ($item) {
			if (is_null($item->country_code)) {
				$geo = ClickGeo::findGeo($item->ip_address); // returns ['isoCode' => 'US'] per your code
				$item->country_code = $geo['isoCode'] ?? null;
			}
			return $item;
		});

		return $this->aggregateByCountry($rows);
	}

	/** Build clicks subquery, optionally filtering by offer */
	protected function buildClicksSubquery($start, $end, ?int $offerId): Builder|_IH_Click_QB {
		$q = Click::query()
		          ->whereBetween('clicks.first_timestamp', [$start, $end])
		          ->where('clicks.click_type', '!=', 2)
		          ->leftJoin('click_vars', 'click_vars.click_id', '=', 'clicks.idclicks')
		          ->selectRaw('
	                clicks.idclicks,
	                clicks.ip_address,
	                clicks.country_code,
	                clicks.click_type,
	                COUNT(clicks.idclicks) AS clicks,
	                SUM(clicks.click_type = 0) AS unique_clicks'
		          )
		          ->groupBy('clicks.ip_address', 'clicks.country_code');

		if ($offerId !== null) {
			$q->where('clicks.offer_idoffer', '=', $offerId);
		}

		return $q;
	}

	/** Build conversions subquery, optionally filtering by offer */
	protected function buildConversionsSubquery($start, $end, ?int $offerId): Builder|_IH_Conversion_QB {
		$q = Conversion::query()
		               ->whereBetween('conversions.timestamp', [$start, $end])
		               ->leftJoin('clicks', 'clicks.idclicks', '=', 'conversions.click_id')
		               ->selectRaw('
		                clicks.ip_address,
		                clicks.country_code,
		                COUNT(conversions.id) AS conversions'
		               )
		               ->groupBy('clicks.ip_address', 'clicks.country_code');

		if ($offerId !== null) {
			$q->where('clicks.offer_idoffer', '=', $offerId);
		}

		return $q;
	}

	/** Collapse IP-level rows into country totals */
	protected function aggregateByCountry(Collection $rows): array
	{
		$reports = [];

		foreach ($rows as $item) {
			$code = $item->country_code ?? 'ZZ'; // Unknown
			if (!isset($reports[$code])) {
				$reports[$code] = [
					'country_code'      => $code,
					'total_clicks'      => 0,
					'unique_clicks'     => 0,
					'total_conversions' => 0,
				];
			}
			$reports[$code]['total_clicks']      += (int) $item->total_clicks;
			$reports[$code]['unique_clicks']     += (int) $item->unique_clicks;
			$reports[$code]['total_conversions'] += (int) $item->total_conversions;
		}

		// Optional: sort by conversions desc
		uasort($reports, function ($a, $b) {
			return $b['total_conversions'] <=> $a['total_conversions'];
		});

		return $reports;
	}
}
