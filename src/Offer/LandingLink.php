<?php

namespace LeadMax\TrackYourStats\Offer;

class LandingLink
{
    public static function isValidLandingUrl($url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $parts = parse_url($url);
        return in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)
            && !empty($parts['host'])
            && !isset($parts['query'])
            && !isset($parts['fragment'])
            && !isset($parts['user']);
    }

    public static function build($landingUrl, $affiliateId, $offerId)
    {
        return $landingUrl . '?a=' . rawurlencode((string) $affiliateId)
            . '&o=' . rawurlencode((string) $offerId)
            . '&n=';
    }
}
