<?php

namespace App\Http\Middleware;

use Closure;
use App\Privilege;
use LeadMax\TrackYourStats\User\Permissions;
use LeadMax\TrackYourStats\System\Session;

class LegacyPermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next, ...$permissions)
    {
        foreach ($permissions as $permission) {
            // God always has announcement access, including with older cached permissions.
            if ($permission === Permissions::CREATE_ANNOUNCEMENTS && (string) Session::userType() === (string) Privilege::ROLE_GOD) {
                continue;
            }
            if (Session::permissions()->can($permission) == false) {
                return redirect('/dashboard');
            }
        }


        return $next($request);
    }
}
