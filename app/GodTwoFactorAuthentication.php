<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GodTwoFactorAuthentication extends Model
{
    protected $table = 'god_two_factor_authentication';

    protected $fillable = [
        'rep_id',
        'encrypted_secret',
        'recovery_codes',
        'last_used_timestep',
        'confirmed_at',
    ];

    protected $hidden = [
        'encrypted_secret',
        'recovery_codes',
    ];

    protected $casts = [
        'confirmed_at' => 'datetime',
        'last_used_timestep' => 'integer',
    ];
}
