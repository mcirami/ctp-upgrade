<?php

namespace LeadMax\TrackYourStats\Clicks\URLEvents\Listeners;

use LeadMax\TrackYourStats\Clicks\UID;
use LeadMax\TrackYourStats\Clicks\URLEvents\ConversionRegistrationEvent;

class ConversionListener extends Listener
{

    public $GETRequirements = ["clickid"];


    public function dispatch()
    {
        $rawClickId = $_GET['clickid'] ?? null;
        if (!is_string($rawClickId) || strlen($rawClickId) > 20
            || !ctype_digit(UID::decode($rawClickId))) {
            return response()->json(['status' => 422, 'message' => 'Invalid click ID.'], 422);
        }
        $customPayout = (isset($_GET["price"]) ? $_GET["price"] : false);
        $clickId      = UID::decode($_GET["clickid"]);
        $register     = new ConversionRegistrationEvent($clickId, $customPayout);

        return $register->fire();
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