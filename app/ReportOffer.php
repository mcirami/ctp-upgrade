<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/** Read-only offer identities for reports, including permanently deleted offers. */
class ReportOffer extends Model
{
    protected $table = 'report_offers';
    protected $primaryKey = 'idoffer';
    public $timestamps = false;

    protected static function booted(): void
    {
        static::saving(function () {
            throw new \LogicException('Report offer identities are read-only.');
        });
        static::deleting(function () {
            throw new \LogicException('Report offer identities are read-only.');
        });
    }
}
