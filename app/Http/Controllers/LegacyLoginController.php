<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use LeadMax\TrackYourStats\System\Session;
use LeadMax\TrackYourStats\User\Login;
use LeadMax\TrackYourStats\User\User;
use LeadMax\TrackYourStats\System\Company;

class LegacyLoginController extends Controller
{

	public function showLoginForm(Request $request)
	{
		$user = new User();
		$company = Company::loadFromSession();
		$company->reloadSettings();

		if ($user->is_loggedin() && $user->verify_login_session()) {
			return redirect('dashboard');
		}

		$user->checkLoginAttempts();

		return $this->loginView($user, $company);
	}

	public function login(Request $request)
	{
		$user = new User();
		$company = Company::loadFromSession();
		$company->reloadSettings();

		$user->checkLoginAttempts();

		$error = null;
		if ($user->count < 5) {
			$username = $request->input('txt_uname_email');
			$password = $request->input('txt_password');

			$result = $user->login($username, $username, $password);

			if ($result == Login::RESULT_SUCCESS) {
				// At this point legacy login session is created.
				// Now check if this user is God/Admin and requires 2FA.
				//$loggedInUser = new User(); // or however you load the current user in your legacy system
				// If you have a method like loadFromSession(), use that instead:
				$loggedInUser = Session::user();

				if ($loggedInUser->requiresTwoFactor()) {

					// If 2FA is enabled, require challenge
					if ( $loggedInUser->two_factor_enabled ) {
						session( [
							'2fa.required' => true,
							'2fa.passed'   => false,
						] );

						return redirect()->route( '2fa.challenge' );
					}

					// If 2FA not enabled yet, force enrollment for these roles
					return redirect()->route( '2fa.enroll' );
				}

				// Non-top-level roles continue normally
				if ($request->has('redirectUri')) {
					return redirect(urldecode($request->get('redirectUri')));
				}

				return redirect('dashboard');

			} elseif ($result == Login::RESULT_PENDING) {
				return redirect('signup_success.php?pending=1');
			} else {
				$user->badLoginAttempt();

				if ($result == Login::RESULT_INVALID_CRED) {
					$error = "Wrong Details ! <p>You have {$user->count} / 5 login attempts remaining. </p>";
				} elseif ($result == Login::RESULT_BANNED) {
					$error = "This account has been banned. Login attempt has been logged and an administrator will be notified. ";
				}
			}
		} else {
			if ($request->has('button')) {
				$error = "You have {$user->count} / 5 login attempts remaining. Please wait until tomorrow or contact an admin to reset your login attempt and/or password.";
			}
		}

		return $this->loginView($user, $company, $error);
	}

	private function loginView(User $user, Company $company, $error = null)
	{
		if ($company->login_theme != '') {
			$filePath = public_path('login_themes/'.$company->login_theme.'/index.php');
			if (file_exists($filePath)) {
				ob_start();
				include $filePath;
				return response(ob_get_clean());
			}
		}

		return view('auth.login', [
			'webroot' => getWebRoot(),
			'user' => $user,
			'error' => $error,
		]);
	}
    public function logout()
    {
        if (isset($_GET["adminLogin"])) {
            unset($_SESSION["adminLogin"]);

            return '<script type="text/javascript">window.close();</script>';
        }


        $user_logout = new \LeadMax\TrackYourStats\User\User();

        $user_logout->logout();

        return redirect('/login');
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
