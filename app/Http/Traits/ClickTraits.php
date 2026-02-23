<?php

namespace App\Http\Traits;

use App\ClickGeoCache;
use LeadMax\TrackYourStats\Clicks\ClickGeo;
use LeadMax\TrackYourStats\User\Permissions;

trait ClickTraits {

	/**
	 * Apply the default formatting for the Query results, used by between(..)
	 * This is public so if the consumer wishes to modify the original query(..), they can and will be able to use
	 * the default formatting.
	 * @param object $results
	 * @return object
	 */
	public function formatResults(object $results): object {
		$per = Permissions::loadFromSession();
		if ($per->can("view_fraud_data")) {
			foreach ($results as $row => $val) {

				if ($val->isoCode) {
					$geo = ClickGeoCache::query()
					                    ->where('ip_address', $val->ip_address)->first();
					if($geo) {
						$geo = $geo->toArray();
					} else {
						$geo = ClickGeo::findGeo($val->ip_address);
					}
				} else {
					$geo = ClickGeo::findGeo($val->ip_address);
				}

				foreach ($geo as $key => $val2) {
					$val->$key = $val2;
				}
			}
		} else {
			foreach ($results as $row => $val) {
				$geo = ClickGeo::findGeo($val->ip_address);
				$val->isoCode = $geo["isoCode"];
				unset($val->ip);
			}
		}

		return $results;
	}
}
