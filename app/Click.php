<?php

namespace App;

use Database\Factories\ClickFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

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

    public $timestamps = false;

    protected $primaryKey = 'idclicks';

	/**
	 * Create a new factory instance for the model.
	 */
	protected static function newFactory(): Factory
	{
		return ClickFactory::new();
	}

}
