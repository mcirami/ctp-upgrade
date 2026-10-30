<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

// Stored in the tenant database alongside its users and permissions.
class LanderUrl extends Model
{
    protected $table = 'lander_urls';
    protected $fillable = ['url', 'status'];
    protected $casts = ['status' => 'boolean'];
}
