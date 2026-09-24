<?php

namespace App\Http\Controllers;


use LeadMax\TrackYourStats\System\Company;
use LeadMax\TrackYourStats\System\Session;
use LeadMax\TrackYourStats\User\Permissions;

class DashboardController extends Controller
{

    public function home()
    {

        $with = [
            'announcements' => (string) Session::userType() === (string) \App\Privilege::ROLE_AFFILIATE
                ? \App\Announcement::query()->orderByDesc('is_pinned')->orderByDesc('created_at')->orderByDesc('id')->get()
                : collect(),
            'canViewPostback' => Session::permissions()->can(Permissions::VIEW_POSTBACK),
            'postBackURL' => getWebRoot()."?uid=".Company::loadFromSession()->getUID()."&clickid=",
            'userId' => Session::userID(),
            'firstName' => Session::userData()->first_name,
            'email' => Session::userData()->email,
	        'userType' => Session::userType(),
	        'domain' => request()->getSchemeAndHttpHost() . "/signup.php?mid=",
        ];

        return view('home', $with);
    }

}