<?php

namespace App;

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
    protected $table = 'conversions';
    public $timestamps = false;




}
