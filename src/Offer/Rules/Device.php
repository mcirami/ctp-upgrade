<?php

namespace LeadMax\TrackYourStats\Offer\Rules;

use Detection\MobileDetect;
use LeadMax\TrackYourStats\Offer\Caps;

class Device implements Rule
{
    public const OPTIONS = [
        'windows_desktop' => 'Windows Desktop',
        'apple_desktop' => 'Apple Desktop',
        'desktop' => 'All Desktop',
        'ios_mobile' => 'iOS Mobile',
        'android_mobile' => 'Android Mobile',
        'mobile' => 'All Mobile',
    ];

    private $rules = [];
    public $redirectOffer = 0;

    public function __construct($rows)
    {
        foreach ($rows as $row) {
            if ($row['type'] !== 'device' || !$row['is_active']) {
                continue;
            }
            $id = $row['idrule'];
            if (!isset($this->rules[$id])) {
                $this->rules[$id] = $row;
                $this->rules[$id]['cap'] = $row['device_cap'] ?? $row['cap'];
                $this->rules[$id]['cap_status'] = $row['device_cap_status'] ?? $row['cap_status'];
                $this->rules[$id]['devices'] = [];
            }
            $this->rules[$id]['devices'][] = $row['device_type'];
        }
        ksort($this->rules, SORT_NUMERIC);
    }

    public static function categories(MobileDetect $detect): array
    {
        $agent = $detect->getUserAgent() ?? '';
        if ($agent !== '' && $detect->isMobile()) {
            if ($detect->isWindowsPhoneOS()) {
                return ['mobile'];
            }
            if ($detect->isAndroidOS()) {
                return ['mobile', 'android_mobile'];
            }
            if ($detect->isiOS() || $detect->isiPadOS()) {
                return ['mobile', 'ios_mobile'];
            }
            return ['mobile'];
        }
        if (stripos($agent, 'Windows NT') !== false) {
            return ['desktop', 'windows_desktop'];
        }
        if (stripos($agent, 'Macintosh') !== false) {
            return ['desktop', 'apple_desktop'];
        }
        return ['desktop'];
    }

    public function getRedirectOffer()
    {
        return $this->redirectOffer;
    }

    public function checkRules()
    {
        if (!$this->rules) {
            return true;
        }
        $categories = self::categories(new MobileDetect());
        foreach ($this->rules as $rule) {
            $matches = (bool) array_intersect($categories, $rule['devices']);
            if ($rule['deny'] ? $matches : !$matches) {
                $this->redirectOffer = $rule['redirect_offer'];
                return false;
            }
            if (!$rule['deny'] && $rule['cap_status'] && !(new Caps($rule['offer_idoffer']))->checkDeviceCap($rule)) {
                $this->redirectOffer = $rule['redirect_offer'];
                return false;
            }
        }
        return true;
    }
}
