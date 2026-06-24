<?php

namespace LeadMax\TrackYourStats\Clicks\URLEvents\Listeners;

use LeadMax\TrackYourStats\Clicks\UID;
use LeadMax\TrackYourStats\Clicks\URLEvents\ConversionRegistrationEvent;
use LeadMax\TrackYourStats\Clicks\URLEvents\ValueRoutedConversionRegistrationEvent;

class ConversionListener extends Listener
{

    public $GETRequirements = ["clickid"];


    public function dispatch()
    {
        $clickId = (int) UID::decode($_GET["clickid"]);

        if ($this->shouldRouteByValue()) {
            $targetOfferId = (int) config('services.postback_value_sale.offer_id');

            if ($targetOfferId <= 0) {
                return response()->json([
                    'status' => 500,
                    'message' => 'POSTBACK_VALUE_SALE_OFFER_ID is not configured.',
                ], 500);
            }

            $register = new ValueRoutedConversionRegistrationEvent(
                $clickId,
                (float) $_GET['value'],
                $targetOfferId
            );

            return $register->fire();
        }

        $customPayout = (isset($_GET["price"]) ? $_GET["price"] : false);
        $register = new ConversionRegistrationEvent($clickId, $customPayout);

        return $register->fire();
    }

    private function shouldRouteByValue(): bool
    {
        if (!isset($_GET['value']) || !is_numeric($_GET['value'])) {
            return false;
        }

        $configuredValue = config('services.postback_value_sale.value');

        return is_numeric($configuredValue)
            && (float) $_GET['value'] === (float) $configuredValue;
    }

    public function shouldBeDispatched()
    {
        if ($this->checkGETRequirements()) {
            if (isset($_GET["function"]) == false || $_GET["function"] == "") {
                return true;
            }
        }

        return false;
    }

}
