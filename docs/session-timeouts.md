# Login session timeout

Set the idle timeout in the deployed application's `.env`, in **minutes**:

```dotenv
SESSION_LIFETIME=120
```

This example allows two hours of inactivity. The default is 1440 minutes (24 hours), preserving the previous login-record timeout. Authenticated page requests refresh the login record's last activity. Expired sessions redirect to `/login`; expired browser form tokens also redirect there. JSON CSRF failures retain their 419 response.

After changing the value, refresh Laravel's configuration:

```sh
php artisan config:cache
```

The application still has two session systems: native PHP (`PHPSESSID`) holds the login identity, while Laravel sessions hold data such as CSRF tokens. `SESSION_DRIVER` only configures Laravel's system. `SESSION_LIFETIME` now controls Laravel's lifetime, the login-record idle check, and native PHP's `session.gc_maxlifetime` at startup. Previously these were unrelated: Laravel was hard-coded to 86400 **minutes**, the login check to 86400 **seconds**, and native PHP inherited the server's configuration.

If production still expires earlier, inspect the configuration used by the website's PHP-FPM/Apache process (CLI PHP can load a different configuration):

- `session.gc_maxlifetime` and any hosting-panel or cron session cleanup policy. For the two-hour example, server cleanup must retain sessions for at least 7200 seconds.
- `session.save_path`: other applications sharing this directory can remove sessions using a shorter lifetime. Configure a dedicated writable session directory in the site's PHP settings if needed; changing the path signs existing users out.
- `session.cookie_lifetime`: a short fixed cookie expiry can end login even during activity. A value of `0` makes the native session cookie last for the browser session, with the application enforcing the idle timeout.
- If requests go to multiple servers, native PHP session storage must be shared or requests must consistently reach the same server. Changing Laravel's session driver alone does not fix native PHP storage.

Production settings have not been inspected or changed by this patch. The earlier production timeout may be server cleanup, cookie expiry, or lost session storage; the code alone cannot establish which.

Regression checks (isolated in-memory database):

```sh
php tests/verify-session-expiry.php
```
