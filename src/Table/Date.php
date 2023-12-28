<?php

namespace LeadMax\TrackYourStats\Table;

// class to hold date functions

use Carbon\Carbon;

class Date
{

    static function addHis(&$dateFrom, &$dateTo)
    {
        $dateFrom .= " 00:00:00";
        $dateTo .= " 23:59:59";
    }

    static function today()
    {
        $timezone = (isset($_COOKIE["timezone"])) ? $_COOKIE["timezone"] : "America/Los_Angeles";
        $date = Carbon::today($timezone);


        return $date->format("Y-m-d");
    }

    static function convertDateTimezone($date, $timezone = "America/Los_Angeles", $format = "Y-m-d H:i:s", $newFormat = null)
    {
        $newFormat = ($newFormat === null) ? $format : $newFormat;

        $carbon = Carbon::createFromFormat($format, $date, $timezone);
        $carbon->setTimezone("UTC");


        return $carbon->format($newFormat);
    }

    static function tomorrow()
    {
        $timezone = (isset($_COOKIE["timezone"])) ? $_COOKIE["timezone"] : "America/Los_Angeles";
        $date = Carbon::tomorrow($timezone);

        return $date->format("Y-m-d");
    }

    public static function getSalesWeek()
    {


        $thisWeek = Carbon::createFromFormat("U", date("U"));

        $monday = Date::convertDateTimezone($thisWeek->startOfWeek()->format("Y-m-d H:i:s"));

        $sunday = Date::convertDateTimezone($thisWeek->endOfWeek()->format("Y-m-d H:i:s"));


        return ['start' => $monday, 'end' => $sunday];
    }

    public static function convertTimestampToEpoch($date)
    {
        return Carbon::createFromFormat("Y-m-d H:i:s", $date)->format("U");
    }

    public static function getSalesWeekEpoch()
    {
        $thisWeek = Carbon::createFromFormat("U", date("U"));

        $monday = self::convertDateTimezone($thisWeek->startOfWeek()->format("U"), "PST", "U", "U");

        $sunday = self::convertDateTimezone($thisWeek->endOfWeek()->format("U"), "PST", "U", "U");

        return ['start' => $monday, 'end' => $sunday];
    }

}
