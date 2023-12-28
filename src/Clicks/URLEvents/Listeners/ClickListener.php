<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 3/21/2018
 * Time: 2:24 PM
 */

namespace LeadMax\TrackYourStats\Clicks\URLEvents\Listeners;

use LeadMax\TrackYourStats\Clicks\URLEvents\ClickRegistrationEvent;

class ClickListener extends Listener
{

    public $GETRequirements = ["repid", "offerid", "function"];


    public function dispatch()
    {
        $register = new ClickRegistrationEvent($_GET["repid"], $_GET["offerid"], $_GET);

        return $register->fire();
    }


    public function shouldBeDispatched()
    {
        if ($this->checkGETRequirements()) {
            if ($_GET["function"] == ClickRegistrationEvent::getEventString()) {
                return true;
            }
        }

        return false;
    }

}