<?php

namespace App\Services;

use App\ClickGeoCache;
use LeadMax\TrackYourStats\Clicks\ClickGeo;
use Illuminate\Support\Collection;
class ClickGeoCacheService {
	/**
	 * Ensure ClickGeoCache has country_code rows for the given IPs.
	 */
	public function warm(Collection|array $ips): void
	{
		$ips = collect($ips)->filter()->unique()->values();

		if ($ips->isEmpty()) {
			return;
		}

		// Pull cached IPs as a collection so diff() stays collection-native.
		$ips->chunk(1000)->each(function ($chunk) {
			$cachedIps = ClickGeoCache::query()
			                          ->whereIn( 'ip_address', $chunk )
			                          ->pluck( 'ip_address' );

			$missing = $chunk->diff( $cachedIps );

			foreach ( $missing as $ip ) {
				$geo = ClickGeo::findGeo( $ip );

				if ( ! empty( $geo['isoCode'] ) ) {
					ClickGeoCache::updateOrCreate(
						[ 'ip_address' => $ip ],
						[ 'country_code' => $geo['isoCode'] ]
					);
				}
			}
		});
	}
}