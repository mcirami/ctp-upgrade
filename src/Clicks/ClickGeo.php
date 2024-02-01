<?php
/**
 * Created by PhpStorm.
 * User: dean
 * Date: 8/9/2017
 * Time: 12:14 PM
 */

namespace LeadMax\TrackYourStats\Clicks;

use GeoIp2\Database\Reader;

function unKnownGeo( array $geo ): array {
	$geo["isoCode"] = "UNKNOWN";

	$geo["subDivision"] = "UNKNOWN";

	$geo["city"] = "UNKNOWN";

	$geo["postal"] = "UNKNOWN";

	$geo["latitude"] = "UNKNOWN";

	$geo["longitude"] = "UNKNOWN";

	return $geo;
}

class ClickGeo
{


    // INPUT: IP Address
    // OUTPUT: array with much geo info
    static function findGeo($ip)
    {
        $geo = array();
        if ($ip == "") {
	        return unKnownGeo($geo);
        }

        $reader = new Reader(env("GEO_IP_DATABASE"));

        try {
            $record = $reader->city($ip);

            $geo["isoCode"] = $record->country->isoCode."\n"; // 'US'

            $geo["subDivision"] = $record->mostSpecificSubdivision->name;

            $geo["city"] = $record->city->name;

            $geo["postal"] = $record->postal->code;

            $geo["latitude"] = $record->location->latitude;

            $geo["longitude"] = $record->location->longitude;
        } catch (\Exception $e) {

            $geo = unKnownGeo($geo);
        }


        return $geo;

    }
}