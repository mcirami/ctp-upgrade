<?php

namespace App;

use Database\Factories\ClickFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

/**
 * App\Click
 *
 * @mixin \Eloquent
 * @property int $idclicks
 * @property string|null $first_timestamp
 * @property int $rep_idrep
 * @property int $offer_idoffer
 * @property string|null $ip_address
 * @property string $browser_agent
 * @property int $click_type
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Click whereBrowserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Click whereClickType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Click whereFirstTimestamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Click whereIdclicks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Click whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Click whereOfferIdoffer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Click whereRepIdrep($value)
 */
class Click extends Model
{
	use HasFactory;
	    const TYPE_UNIQUE = 0;
	    const TYPE_RAW = 1;
	    const TYPE_BLACKLISTED = 2;
	    const TYPE_GENERATED = 3;
	    public const GEO_COUNTRY_CODE_SQL = 'COALESCE(clicks.country_code, geo.country_code)';

    public $timestamps = false;

    protected $primaryKey = 'idclicks';

	/**
	 * Create a new factory instance for the model.
	 */
	protected static function newFactory(): Factory
	{
		return ClickFactory::new();
	}

	protected static function booted()
	{
		static::addGlobalScope('ignore_old_records', function (Builder $builder) {
			$builder->where('first_timestamp', '>=', DB::raw('NOW() - INTERVAL 6 MONTH'));
		});
	}

	public static function missingCountryCodeIps(string $startDate, string $endDate): Collection
	{
		return static::query()
		             ->whereBetween('first_timestamp', [$startDate, $endDate])
		             ->where('click_type', '!=', self::TYPE_BLACKLISTED)
		             ->whereNull('country_code')
		             ->distinct()
		             ->pluck('ip_address');
	}

	public function scopeCountryClicksInGeo(
		Builder $query,
		string $startDate,
		string $endDate,
		?string $geoCode = null
	): Builder {
		return $query
			->leftJoin('click_vars', 'click_vars.click_id', '=', 'clicks.idclicks')
			->leftJoin('conversions', 'conversions.click_id', '=', 'clicks.idclicks')
			->leftJoin('click_geo_cache as geo', 'geo.ip_address', '=', 'clicks.ip_address')
			->join('rep', 'rep.idrep', '=', 'clicks.rep_idrep')
			->leftJoin('offer', 'offer.idoffer', '=', 'clicks.offer_idoffer')
			->whereBetween('first_timestamp', [$startDate, $endDate])
			->where('clicks.click_type', '!=', self::TYPE_BLACKLISTED)
			->when($geoCode, function (Builder $builder) use ($geoCode) {
				$builder->whereRaw(
					self::GEO_COUNTRY_CODE_SQL . ' = ?',
					[$geoCode]
				);
			})
			->select([
				'clicks.idclicks',
				'clicks.first_timestamp',
				'conversions.timestamp as conversion_timestamp',
				'conversions.paid',
				'click_vars.sub1',
				'click_vars.sub2',
				'click_vars.sub3',
				'clicks.rep_idrep',
				'clicks.offer_idoffer',
				'clicks.referer',
				'geo.ip_address as click_geo_ip',
			])
			->orderByDesc('conversions.paid');
	}

	public function scopeCountryClicksByOfferInGeo(
		Builder $query,
		string $startDate,
		string $endDate,
		?string $geoCode = null
	): Builder {
		$geoCountryCode = self::GEO_COUNTRY_CODE_SQL;

		return $query
			->whereBetween('first_timestamp', [$startDate, $endDate])
			->where('clicks.click_type', '!=', self::TYPE_BLACKLISTED)
			->leftJoin('click_geo_cache as geo', 'geo.ip_address', '=', 'clicks.ip_address')
			->leftJoin('offer', 'offer.idoffer', '=', 'clicks.offer_idoffer')
			->selectRaw("
				offer.offer_name,
				clicks.offer_idoffer AS offer_id,
				{$geoCountryCode} AS country_code,
				COUNT(*) AS total_clicks,
				SUM(clicks.click_type = 0) AS unique_clicks
			")
			->when($geoCode, fn (Builder $builder) => $builder->whereRaw("{$geoCountryCode} = ?", [$geoCode]))
			->groupBy('clicks.offer_idoffer', DB::raw($geoCountryCode));
	}

	public function scopeCountryClicksByIpInGeo(
		Builder $query,
		string $startDate,
		string $endDate,
		?int $repId = null,
		?int $offerId = null
	): Builder {
		$geoCountryCode = self::GEO_COUNTRY_CODE_SQL;

		return $query
			->whereBetween('first_timestamp', [$startDate, $endDate])
			->where('clicks.click_type', '!=', self::TYPE_BLACKLISTED)
			->when(!is_null($repId), fn (Builder $builder) => $builder->where('rep_idrep', '=', $repId))
			->when(!is_null($offerId), fn (Builder $builder) => $builder->where('offer_idoffer', '=', $offerId))
			->leftJoin('click_geo_cache as geo', 'geo.ip_address', '=', 'clicks.ip_address')
			->select(
				'clicks.ip_address',
				DB::raw($geoCountryCode . ' as country_code'),
				DB::raw('COUNT(idclicks) as clicks'),
				DB::raw('SUM(clicks.click_type = ' . self::TYPE_UNIQUE . ') as unique_clicks')
			)
			->groupBy('clicks.ip_address', DB::raw($geoCountryCode));
	}

}
