<?php

namespace Tests;

use LeadMax\TrackYourStats\Offer\Rules\Device;
use LeadMax\TrackYourStats\Offer\Rules\Handlers\Device as DeviceHandler;
use PHPUnit\Framework\TestCase;

class DeviceRuleTest extends TestCase
{
    private function row($device, $overrides = [])
    {
        return $overrides + [
            'idrule' => 1, 'type' => 'device', 'is_active' => 1, 'device_type' => $device,
            'deny' => 0, 'cap' => 0, 'cap_status' => 0, 'redirect_offer' => 42, 'offer_idoffer' => 1,
        ];
    }

    protected function tearDown(): void
    {
        unset($_SERVER['HTTP_USER_AGENT']);
        parent::tearDown();
    }

    public function test_create_payload_converts_cap_fields_to_integers(): void
    {
        foreach ([['', false, 0, 0], ['25', true, 25, 1]] as [$amount, $enabled, $expectedAmount, $expectedStatus]) {
            $handler = new DeviceHandler([1, 'Mobile only', 2, false, $amount, $enabled, 'mobile']);
            $this->assertSame($expectedAmount, $handler->capAmount);
            $this->assertSame($expectedStatus, $handler->capStatus);
        }
    }

    public function test_each_choice_allows_and_denies_the_expected_traffic(): void
    {
        $agents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64)' => ['desktop', 'windows_desktop'],
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)' => ['desktop', 'apple_desktop'],
            'Mozilla/5.0 (X11; Linux x86_64)' => ['desktop'],
            'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) Mobile/15E148' => ['mobile', 'ios_mobile'],
            'Mozilla/5.0 (iPad; CPU OS 17_0 like Mac OS X) Mobile/15E148' => ['mobile', 'ios_mobile'],
            'Mozilla/5.0 (Linux; Android 14; Pixel 8) Mobile Safari/537.36' => ['mobile', 'android_mobile'],
            'Mozilla/5.0 (Linux; Android 13; SM-X200) Chrome/115.0 Safari/537.36' => ['mobile', 'android_mobile'],
            'Mozilla/5.0 (Windows Phone 10.0; Android 6.0.1; Microsoft; Lumia 950)' => ['mobile'],
        ];
        foreach ($agents as $agent => $categories) {
            $_SERVER['HTTP_USER_AGENT'] = $agent;
            foreach (array_keys(Device::OPTIONS) as $device) {
                foreach ([0, 1] as $deny) {
                    $rule = new Device([$this->row($device, ['deny' => $deny])]);
                    $allowed = in_array($device, $categories) !== (bool) $deny;
                    $this->assertSame($allowed, $rule->checkRules(), "$device, deny=$deny, $agent");
                    $this->assertSame($allowed ? 0 : 42, $rule->getRedirectOffer());
                }
            }
        }
    }

    public function test_multiple_selections_and_first_failing_active_rule(): void
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)';
        $rows = [
            $this->row('windows_desktop'), $this->row('apple_desktop'),
            $this->row('mobile', ['idrule' => 2, 'is_active' => 0]),
        ];
        $this->assertTrue((new Device($rows))->checkRules());
        $rows[] = $this->row('desktop', ['idrule' => 3, 'deny' => 1, 'redirect_offer' => 99]);
        $rule = new Device(array_reverse($rows));
        $this->assertFalse($rule->checkRules());
        $this->assertSame(99, $rule->getRedirectOffer());
    }
}
