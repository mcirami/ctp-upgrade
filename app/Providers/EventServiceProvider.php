<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Auth\Events\Login;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\SomeEvent' => [
            'App\Listeners\EventListener',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //parent::boot();

	    Event::listen(Login::class, function (Login $event) {
		    $user = $event->user;

		    if ($user->requiresTwoFactor() && $user->two_factor_enabled) {
			    session([
				    '2fa:user:id' => $user->id,
				    '2fa:remember' => request()->boolean('remember'),
			    ]);

			    auth()->logout();

			    // Important: regenerate session to avoid fixation
			    request()->session()->invalidate();
			    request()->session()->regenerateToken();

			    // Re-store 2FA session keys after invalidate:
			    session([
				    '2fa:user:id' => $user->id,
				    '2fa:remember' => request()->boolean('remember'),
			    ]);

			    // Redirect cannot happen directly here cleanly,
			    // so we flag it and handle in LoginController response.
		    }
	    });
    }
}
