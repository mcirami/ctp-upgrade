<?php

namespace App;

use Database\Factories\ConversionFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Conversion
 *
 * @mixin \Eloquent
 * @property int $id
 * @property int $user_id
 * @property int $click_id
 * @property string $timestamp
 * @property float $paid
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Conversion whereClickId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Conversion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Conversion wherePaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Conversion whereTimestamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Conversion whereUserId($value)
 */
class Conversion extends Model
{
	use HasFactory;
    protected $table = 'conversions';
    public $timestamps = false;

	/**
	 * Create a new factory instance for the model.
	 */
	protected static function newFactory(): Factory
	{
		return ConversionFactory::new();
	}

}
