<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use LeadMax\TrackYourStats\User\User;

class LegacyUserAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  Request $request
     * @param  Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next): mixed {
        $user = new User();
        if ($user->verify_login_session()) {
            return $next($request);
        } else {
            return redirect('/login');
        }
    }
}
