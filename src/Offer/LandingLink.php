<?php

namespace LeadMax\TrackYourStats\Offer;

class LandingLink
{
    public static function isValidBaseUrl($url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $parts = parse_url($url);
        return in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)
            && !empty($parts['host'])
            && in_array($parts['path'] ?? '', ['', '/'], true)
            && !isset($parts['query'])
            && !isset($parts['fragment'])
            && !isset($parts['user']);
    }

    public static function pathForHost($host)
    {
        $host = strtolower(explode(':', $host)[0]);
        if (preg_match('/(^|\.)galaxywebrevenue2\.com$/', $host)) {
            return 'g2';
        }
        if (preg_match('/(^|\.)galaxywebrevenue4\.com$/', $host)) {
            return 'g4';
        }

        return 'r';
    }

    public static function build($baseUrl, $host, $affiliateId, $offerId)
    {
        return rtrim($baseUrl, '/') . '/' . self::pathForHost($host)
            . '/?a=' . rawurlencode((string) $affiliateId)
            . '&o=' . rawurlencode((string) $offerId)
            . '&n=';
    }
}
