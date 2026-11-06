<?php

namespace LeadMax\TrackYourStats\Clicks;

use DateTimeImmutable;
use DomainException;

class ConversionPostbackPolicy
{
    public static function validate($offer, $click, int $maxAgeDays, ?DateTimeImmutable $now = null): void
    {
        if (!$offer || (int) $offer->status !== 1) {
            throw new DomainException('Offer is inactive or missing.');
        }

        $timestamp = (string) ($click->first_timestamp ?? '');
        $clickedAt = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $timestamp);
        if (!$clickedAt || $clickedAt->format('Y-m-d H:i:s') !== $timestamp) {
            throw new DomainException('Invalid click timestamp.');
        }

        $now = $now ?? new DateTimeImmutable();
        if ($clickedAt > $now) {
            throw new DomainException('Click timestamp is in the future.');
        }
        // Invalid configuration must not silently disable the age restriction.
        $maxAgeDays = $maxAgeDays > 0 ? $maxAgeDays : 90;
        if ($clickedAt < $now->modify("-{$maxAgeDays} days")) {
            throw new DomainException('Click is outside the conversion window.');
        }
    }
}
