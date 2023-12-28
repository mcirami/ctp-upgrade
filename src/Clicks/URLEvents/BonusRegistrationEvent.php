<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 3/22/2018
 * Time: 10:12 AM
 */

namespace LeadMax\TrackYourStats\Clicks\URLEvents;


use App\User;
use Illuminate\Http\JsonResponse;
use LeadMax\TrackYourStats\User\Bonus;

class BonusRegistrationEvent extends URLEvent
{

    public $bonusId;

    public $userId;

    public function __construct($bonusId, $userId)
    {
        $this->bonusId = $bonusId;
        $this->userId = $userId;
    }


    private function registerBonusToUser()
    {
        return Bonus::registerBonusToUser($this->bonusId, $this->userId);
    }

    public function fire()
    {
        if (User::find($this->userId)->isBanned()) {
            return JsonResponse::create(['status' => 400, 'message' => 'User is banned.']);
        }

        if ($this->registerBonusToUser()) {
            return JsonResponse::create(['status' => 200, 'message' => 'Bonus registered.']);
        } else {
            return JsonResponse::create(['status' => 500, 'message' => 'Unknown error.'], 500);
        }

    }

    public static function getEventString(): string
    {
        return "bonus";
    }

}