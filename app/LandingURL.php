<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LandingURL extends Model
{
    protected $table = 'landing_urls';
    protected $connection = 'master';
    public $timestamps = false;

    protected $fillable = ['url', 'status', 'company_id', 'timestamp'];
}
