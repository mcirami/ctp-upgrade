<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * App\PayoutLog
 *
 * @mixin \Eloquent
 * @property string $date
 * @property int $id
 * @property int $user_id
 * @property float $revenue
 * @property float $deductions
 * @property float $bonuses
 * @property float $referrals
 * @property string|null $start_of_week
 * @property string|null $end_of_week
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PayoutLog whereBonuses($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PayoutLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PayoutLog whereDeductions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PayoutLog whereEndOfWeek($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PayoutLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PayoutLog whereReferrals($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PayoutLog whereRevenue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PayoutLog whereStartOfWeek($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PayoutLog whereStatus($status)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PayoutLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PayoutLog whereUserId($value)
 */
class PayoutLog extends Model
{

    protected $guarded = [
		'id',
		'created_at',
	    'updated_at',
    ];

	/**
	 * The attributes that should be cast.
	 *
	 *
	 */
	protected $casts = [
		'start_of_week'  => 'date',
		'end_of_week'    => 'date',
	];

	public function user() {
		return $this->belongsTo(User::class);
	}


}
