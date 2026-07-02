<?php

namespace LeadMax\TrackYourStats\System;

class IPWhitelist
{
    public static function contains($ipAddress, array $allowedAddresses)
    {
        foreach ($allowedAddresses as $allowedAddress) {
            if (self::matches($ipAddress, $allowedAddress)) {
                return true;
            }
        }

        return false;
    }

    public static function matches($ipAddress, $allowedAddress)
    {
        $ipAddress = trim((string) $ipAddress);
        $allowedAddress = trim((string) $allowedAddress);

        if ($ipAddress === '' || $allowedAddress === '') {
            return false;
        }

        if (strpos($allowedAddress, '/') === false) {
            return inet_pton($ipAddress) !== false
                && inet_pton($ipAddress) === inet_pton($allowedAddress);
        }

        list($network, $prefixLength) = array_pad(explode('/', $allowedAddress, 2), 2, null);
        $network = trim((string) $network);

        if (!ctype_digit((string) $prefixLength)) {
            return false;
        }

        $ipBytes = inet_pton($ipAddress);
        $networkBytes = inet_pton($network);

        if ($ipBytes === false || $networkBytes === false || strlen($ipBytes) !== strlen($networkBytes)) {
            return false;
        }

        $prefixLength = (int) $prefixLength;
        $maxPrefixLength = strlen($ipBytes) * 8;

        if ($prefixLength < 0 || $prefixLength > $maxPrefixLength) {
            return false;
        }

        $fullBytes = intdiv($prefixLength, 8);
        $remainingBits = $prefixLength % 8;

        if ($fullBytes > 0 && substr($ipBytes, 0, $fullBytes) !== substr($networkBytes, 0, $fullBytes)) {
            return false;
        }

        if ($remainingBits === 0) {
            return true;
        }

        $mask = (0xff << (8 - $remainingBits)) & 0xff;

        return (ord($ipBytes[$fullBytes]) & $mask) === (ord($networkBytes[$fullBytes]) & $mask);
    }
}
