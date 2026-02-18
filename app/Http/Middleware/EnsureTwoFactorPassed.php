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

	    // If not logged in, let your existing guest/auth middleware handle it
	    if (!$user->is_loggedin() || !$user->verify_login_session()) {
		    return $next($request);
	    }

		$sessionUser = Session::user();

	    // Only enforce for top-level accounts
	    if ($sessionUser->requiresTwoFactor()) {

		    // If 2FA is enabled for them, require passing the challenge
		    if ($sessionUser->two_factor_enabled && !session('2fa.passed')) {

			    // Prevent redirect loop if already on 2FA pages
			    if (!$request->routeIs('2fa.*')) {
				    return redirect()->route('2fa.challenge');
			    }
		    }

		    // If they require 2FA but haven’t enrolled yet, push them to enroll
		    if (!$sessionUser->two_factor_enabled && !$request->routeIs('2fa.*')) {
			    return redirect()->route('2fa.enroll');
		    }
	    }

	    return $next($request);
    }
}
