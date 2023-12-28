<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use LeadMax\TrackYourStats\User\User;

class LegacyLoginController extends Controller
{

    public function logout()
    {
        if (isset($_GET["adminLogin"])) {
            unset($_SESSION["adminLogin"]);

            return '<script type="text/javascript">window.close();</script>';
        }


        $user_logout = new \LeadMax\TrackYourStats\User\User();

        $user_logout->logout();

        return redirect('/login.php');
    }


    public function adminLogin($userId)
    {
        $user = new User();
        if ($user->hasRep($userId)) {
            $user->adminLogin($userId);

            return redirect('/dashboard?adminLogin');
        } else {
            return redirect('/dashboard');
        }
    }
}
