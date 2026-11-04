<?php

namespace Tests;

use GeoIp2\Model\City;
use GeoIp2\ProviderInterface;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Support\Facades\DB;
use LeadMax\TrackYourStats\Offer\Rules\Geo;
use PHPUnit\Framework\TestCase;

class GeoRuleTest extends TestCase
{
    private function row(int $id, string $country, array $overrides = []): array
    {
        return $overrides + [
            'idrule' => $id, 'type' => 'geo', 'is_active' => 1, 'deny' => 0,
            'offer_idoffer' => 982, 'country_code' => $country, 'cap_status' => 0,
            'redirect_offer' => 1100 + $id,
        ];
    }

    private function geo(array $rows): Geo
    {
        $reader = $this->createStub(ProviderInterface::class);
        $reader->method('city')->willReturn(new City(['country' => ['iso_code' => 'US']]));
        return new Geo($rows, '192.0.2.1', $reader);
    }

    public function test_passing_one_country_rule_continues_to_the_next_active_rule(): void
    {
        $rows = [
            $this->row(1, 'CA'), $this->row(1, 'US'),
            $this->row(2, 'GB', ['deny' => 1]), $this->row(3, 'US', ['deny' => 1, 'is_active' => 0]),
        ];
        $this->assertTrue($this->geo($rows)->checkRules());
        $rows[] = $this->row(4, 'CA');
        $rows[] = $this->row(5, 'US', ['deny' => 1]);
        $rule = $this->geo(array_reverse($rows));
        $this->assertFalse($rule->checkRules());
        $this->assertSame(1104, $rule->getRedirectOffer());
    }

    public function test_matching_country_cap_counts_todays_conversions_for_this_offer(): void
    {
        $manager = new Manager();
        $manager->addConnection(['driver' => 'sqlite', 'database' => ':memory:']);
        DB::swap($manager->getDatabaseManager());
        try {
            DB::statement('CREATE TABLE conversions (click_id INTEGER, timestamp TEXT)');
            DB::statement('CREATE TABLE clicks (idclicks INTEGER, offer_idoffer INTEGER, country_code TEXT, ip_address TEXT)');
            DB::statement("INSERT INTO clicks VALUES (1,982,NULL,'192.0.2.1'), (2,982,'CA','192.0.2.2'), (3,990,'US','192.0.2.3')");
            DB::statement("INSERT INTO conversions VALUES (1,datetime('now')), (1,'2000-01-01'), (2,datetime('now')), (3,datetime('now'))");
            $row = $this->row(4, 'US', ['cap_status' => 1, 'cap' => 2]);
            $this->assertTrue($this->geo([$row])->checkRules());
            DB::statement("INSERT INTO conversions VALUES (1,datetime('now'))");
            $rule = $this->geo([$row]);
            $this->assertFalse($rule->checkRules());
            $this->assertSame(1104, $rule->getRedirectOffer());
        } finally {
            DB::clearResolvedInstance('db');
            $manager->getConnection()->disconnect();
        }
    }
}
