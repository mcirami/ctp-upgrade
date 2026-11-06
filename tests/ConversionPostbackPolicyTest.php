<?php

namespace Tests;

use DateTimeImmutable;
use DomainException;
use LeadMax\TrackYourStats\Clicks\ConversionPostbackPolicy;
use PHPUnit\Framework\TestCase;

class ConversionPostbackPolicyTest extends TestCase
{
    private function validate(?int $status, string $clickedAt, int $days = 90): void
    {
        ConversionPostbackPolicy::validate(
            $status === null ? null : (object) ['status' => $status],
            (object) ['first_timestamp' => $clickedAt],
            $days,
            new DateTimeImmutable('2026-11-05 15:30:07')
        );
    }

    public function test_accepts_active_offer_at_exact_window_boundary(): void
    {
        $this->validate(1, '2026-08-07 15:30:07');
        $this->addToAssertionCount(1);
    }

    public function test_rejects_one_second_outside_window(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('outside the conversion window');
        $this->validate(1, '2026-08-07 15:30:06');
    }

    public function test_rejects_incident_click_even_on_active_offer(): void
    {
        $this->expectException(DomainException::class);
        $this->validate(1, '2025-07-04 02:24:30');
    }

    public function test_rejects_inactive_offer_even_for_recent_click(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('inactive or missing');
        $this->validate(0, '2026-11-05 15:29:00');
    }

    public function test_rejects_missing_offer(): void
    {
        $this->expectException(DomainException::class);
        $this->validate(null, '2026-11-05 15:29:00');
    }

    public function test_rejects_invalid_timestamp(): void
    {
        $this->expectException(DomainException::class);
        $this->validate(1, '2026-09-31 15:29:00');
    }

    public function test_rejects_future_timestamp(): void
    {
        $this->expectException(DomainException::class);
        $this->validate(1, '2026-11-05 15:30:08');
    }

    public function test_invalid_configuration_does_not_disable_restriction(): void
    {
        $this->expectException(DomainException::class);
        $this->validate(1, '2025-07-04 02:24:30', 0);
    }
    public function test_registration_rejects_before_writing_or_sending_callback(): void
    {
        $previousContainer = \Illuminate\Container\Container::getInstance();
        $database = new \Illuminate\Database\Capsule\Manager();
        $database->addConnection(['driver' => 'sqlite', 'database' => ':memory:']);
        $database->bootEloquent();
        $connection = $database->getConnection();
        $connection->statement('CREATE TABLE clicks (idclicks INTEGER, offer_idoffer INTEGER, first_timestamp TEXT)');
        $connection->statement('CREATE TABLE offer (idoffer INTEGER PRIMARY KEY, status INTEGER, offer_type INTEGER)');
        $connection->statement('CREATE TABLE conversions (click_id INTEGER)');
        $connection->statement('CREATE TABLE pending_conversions (click_id INTEGER)');
        \LeadMax\TrackYourStats\Database\DatabaseConnection::changeConnection($connection->getPdo());
        $container = new \Illuminate\Container\Container();
        $container->instance('config', new \Illuminate\Config\Repository(['postbacks' => ['max_click_age_days' => 90]]));
        \Illuminate\Container\Container::setInstance($container);
        try {
            // Exercise both ordinary and pending offers through the actual registration method.
            foreach ([0, 3] as $offerType) {
                foreach ([0, 1] as $status) {
                    $connection->table('offer')->delete();
                    $connection->table('clicks')->delete();
                    $connection->table('offer')->insert(['idoffer' => 946, 'status' => $status, 'offer_type' => $offerType]);
                    $connection->table('clicks')->insert([
                        'idclicks' => 123, 'offer_idoffer' => 946,
                        'first_timestamp' => $status ? '2025-07-03 17:40:12' : date('Y-m-d H:i:s'),
                    ]);
                    $event = new \LeadMax\TrackYourStats\Clicks\URLEvents\ConversionRegistrationEvent(123);
                    $method = new \ReflectionMethod($event, 'registerConversion');
                    try {
                        $method->invoke($event);
                        $this->fail('The postback should have been rejected.');
                    } catch (DomainException $exception) {
                        $this->assertStringContainsString($status ? 'conversion window' : 'inactive', $exception->getMessage());
                    }
                    $this->assertSame(0, $connection->table('conversions')->count());
                    $this->assertSame(0, $connection->table('pending_conversions')->count());
                }
            }
        } finally {
            \LeadMax\TrackYourStats\Database\DatabaseConnection::changeConnection(null);
            \Illuminate\Container\Container::setInstance($previousContainer);
            $connection->disconnect();
        }
    }
}
