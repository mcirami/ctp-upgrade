<?php

namespace App;

use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayoutData extends Model
{
    use HasFactory;

	/**
	 * The attributes that are not mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [
		'id',
		'created_at',
		'updated_at',
		'rep_idrep'
	];

	public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo {
		return $this->belongsTo(User::class);
	}
}
