<?php

namespace App\Http\Middleware;

use LeadMax\TrackYourStats\System\Session;
use LeadMax\TrackYourStats\User\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorPassed
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
	    $user = new User();

	    if (!$user->is_loggedin() || !$user->verify_login_session()) {
		    return $next($request);
	    }

		$sessionUser = Session::user();
		if (!$sessionUser) {
			return redirect('/login');
		}

	    if ($sessionUser->requiresTwoFactor()) {
		    if (!$request->routeIs('2fa.*')) {
			    if (!$sessionUser->two_factor_enabled || !$sessionUser->two_factor_confirmed_at) {
				    return redirect()->route('2fa.enroll');
			    }

			    if (!session('2fa.passed')) {
				    return redirect()->route('2fa.challenge');
			    }
		    }
	    } else {
		    session()->forget(['2fa.required', '2fa.passed', '2fa.redirect_uri']);
	    }

	    return $next($request);
    }
}
