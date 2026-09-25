<?php
/** Session regression checks; only an in-memory SQLite database is used. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__.'/../vendor/autoload.php';

use App\Exceptions\Handler;
use App\Http\Controllers\LegacyLoginController;
use App\Http\Middleware\LegacyUserAuth;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use LeadMax\TrackYourStats\Database\DatabaseConnection;
use LeadMax\TrackYourStats\User\Login;

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['session.driver' => 'array', 'session.lifetime' => 30]);
set_error_handler(function ($severity, $message, $file, $line) {
    if (error_reporting() & $severity) { throw new ErrorException($message, 0, $severity, $file, $line); }
    return false;
});
$checks = 0;
function checkSession($condition, $message) {
    global $checks;
    if (!$condition) { throw new RuntimeException($message); }
    $checks++;
}
function checkLoginRedirect($response) {
    checkSession($response->getStatusCode() === 302 && str_ends_with($response->getTargetUrl(), '/login'), 'Expected login redirect');
}

$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$login = new Login();
foreach ([[], ['repid' => 1], ['salt' => 'fixture']] as $missingSession) {
    $_SESSION = $missingSession;
    checkSession($login->verify_login_session() === false, 'Missing credentials must be unauthenticated without a database');
    checkLoginRedirect((new LegacyUserAuth())->handle(Request::create('/dashboard'), function () {
        throw new RuntimeException('Protected page executed after expiry');
    }));
    checkLoginRedirect((new LegacyLoginController())->logout());
    checkSession($login->logout() === true, 'Repeated logout must be safe');
}
$_GET['adminLogin'] = 1;
$_SESSION = [];
checkLoginRedirect((new LegacyLoginController())->logout());
$_GET = [];

$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec('CREATE TABLE logins (repid INTEGER, session_id TEXT, success INTEGER, last_action_time INTEGER, ip TEXT)');
DatabaseConnection::changeConnection($pdo);
function seedLogin(PDO $pdo, int $age, int $success = 1) {
    $_SESSION = ['repid' => 1, 'salt' => 'fixture', 'user_session' => 'test', 'permissions' => 'cached', 'userData' => 'cached', 'userType' => 3];
    $pdo->exec('DELETE FROM logins');
    $pdo->prepare('INSERT INTO logins VALUES (?, ?, ?, ?, ?)')->execute([1, hash('sha256', 'fixture'), $success, time() - $age, '127.0.0.1']);
}
seedLogin($pdo, 60);
checkSession($login->verify_login_session() === true, 'Active session rejected');
checkSession((int) $pdo->query('SELECT last_action_time FROM logins')->fetchColumn() >= time() - 2, 'Activity was not refreshed');
seedLogin($pdo, 1801);
checkLoginRedirect((new LegacyUserAuth())->handle(Request::create('/dashboard'), function () { throw new RuntimeException('Expired page executed'); }));
checkSession(!isset($_SESSION['repid'], $_SESSION['permissions'], $_SESSION['userData']), 'Expired identity was retained');
checkSession((int) $pdo->query('SELECT success FROM logins')->fetchColumn() === 2, 'Expired login not revoked');
config(['session.lifetime' => 60]);
seedLogin($pdo, 1801);
checkSession($login->verify_login_session() === true, 'Configured lifetime was ignored');
seedLogin($pdo, 10, -1);
checkSession($login->verify_login_session() === false, 'Revoked login was accepted');
seedLogin($pdo, 3601);
checkSession($login->verify_login_session(false) === false && isset($_SESSION['repid']), 'Read-only verification changed credentials');
$_SESSION = [];
$handler = $app->make(Handler::class);
checkLoginRedirect($handler->render(Request::create('/announcements', 'POST'), new TokenMismatchException()));
$json = Request::create('/announcements', 'POST', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
checkSession($handler->render($json, new TokenMismatchException())->getStatusCode() === 419, 'JSON CSRF response changed');
// Exercise the actual route/middleware stack without the database-backed legacy bootstrap.
$_SESSION = [];
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
foreach ([['/dashboard', 'GET'], ['/logout', 'GET'], ['/announcements', 'POST']] as [$path, $method]) {
    checkLoginRedirect($kernel->handle(Request::create($path, $method)));
}
restore_error_handler();
echo "Session expiry checks passed: {$checks}\n";
