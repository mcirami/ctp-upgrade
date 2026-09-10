<?php

namespace App\Http\Controllers;

use App\Services\GodTwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use LeadMax\TrackYourStats\System\Session;
use LeadMax\TrackYourStats\User\Login;

class GodTwoFactorController extends Controller
{
    public function challenge(Request $request, GodTwoFactorService $twoFactor): View|RedirectResponse
    {
        $repId = $this->pendingUserId($request);

        if (!$repId || !$twoFactor->isGod($repId)) {
            return redirect('/login');
        }

        $record = $twoFactor->confirmedRecordFor($repId);

        return view('auth.two-factor-challenge', [
            'configured' => (bool) $record,
            'ipAddress' => $request->ip(),
        ]);
    }

    public function verifyChallenge(Request $request, GodTwoFactorService $twoFactor): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string', 'max:32']]);
        $repId = $this->pendingUserId($request);

        if (!$repId || !$twoFactor->isGod($repId)) {
            return redirect('/login');
        }

        $record = $twoFactor->confirmedRecordFor($repId);

        if (!$record || !$twoFactor->verify($record, (string) $request->input('code'))) {
            return back()->withErrors(['code' => 'That authenticator or recovery code is invalid.'])->withInput();
        }

        if (!(new Login())->completeTwoFactorLogin($repId)) {
            $this->clearPending($request);
            return redirect('/login');
        }

        $destination = $this->safeDestination(
            (string) $request->session()->pull('two_factor_redirect', '/dashboard'),
            $request
        );
        $this->clearPending($request);
        $request->session()->regenerate();

        return redirect($destination);
    }

    public function setup(Request $request, GodTwoFactorService $twoFactor): View
    {
        $this->requireWhitelistedGod($request, $twoFactor);
        $repId = (int) Session::userID();
        $record = $twoFactor->recordFor($repId);

        if ($record && $record->confirmed_at) {
            return view('auth.two-factor-setup', [
                'enabled' => true,
                'ipAddress' => $request->ip(),
                'recoveryCodes' => $request->session()->pull('two_factor_recovery_codes'),
            ]);
        }

        [$record, $secret] = $twoFactor->beginEnrollment($repId);
        $profile = Session::userData();
        $account = $profile->email ?: $profile->user_name;

        return view('auth.two-factor-setup', [
            'enabled' => false,
            'ipAddress' => $request->ip(),
            'secret' => $secret,
            'qrCode' => $twoFactor->qrDataUri((string) $account, $secret),
            'recoveryCodes' => null,
        ]);
    }

    public function confirm(Request $request, GodTwoFactorService $twoFactor): RedirectResponse
    {
        $this->requireWhitelistedGod($request, $twoFactor);
        $request->validate(['code' => ['required', 'digits:6']]);
        $record = $twoFactor->recordFor((int) Session::userID());

        if (!$record || $record->confirmed_at) {
            return redirect()->route('two-factor.setup');
        }

        $recoveryCodes = $twoFactor->confirmEnrollment($record, (string) $request->input('code'));

        if ($recoveryCodes === false) {
            return back()->withErrors(['code' => 'That code did not match. Wait for a new code and try again.']);
        }

        return redirect()->route('two-factor.setup')
            ->with('two_factor_recovery_codes', $recoveryCodes)
            ->with('two_factor_status', 'Google Authenticator is now enabled for unrecognized IP addresses.');
    }

    public function disable(Request $request, GodTwoFactorService $twoFactor): RedirectResponse
    {
        $this->requireWhitelistedGod($request, $twoFactor);
        $request->validate(['code' => ['required', 'string', 'max:32']]);
        $repId = (int) Session::userID();
        $record = $twoFactor->confirmedRecordFor($repId);

        if (!$record || !$twoFactor->verify($record, (string) $request->input('code'))) {
            return back()->withErrors(['code' => 'Enter a valid authenticator or recovery code to disable this protection.']);
        }

        $twoFactor->disable($repId);

        return redirect()->route('two-factor.setup')
            ->with('two_factor_status', 'Google Authenticator has been disabled.');
    }

    private function pendingUserId(Request $request): ?int
    {
        $repId = $request->session()->get('two_factor_pending_user_id');
        $until = (int) $request->session()->get('two_factor_pending_until', 0);
        $ip = (string) $request->session()->get('two_factor_pending_ip', '');

        if (!$repId || $until < now()->timestamp || !hash_equals($ip, (string) $request->ip())) {
            $this->clearPending($request);
            return null;
        }

        return (int) $repId;
    }

    private function clearPending(Request $request): void
    {
        $request->session()->forget([
            'two_factor_pending_user_id',
            'two_factor_pending_ip',
            'two_factor_pending_until',
            'two_factor_redirect',
        ]);
    }

    private function requireWhitelistedGod(Request $request, GodTwoFactorService $twoFactor): void
    {
        abort_unless(
            (int) Session::userType() === \App\Privilege::ROLE_GOD
            && $twoFactor->isRequestWhitelisted($request),
            403,
            'Authenticator settings can only be changed by a God user on a whitelisted IP address.'
        );
    }

    private function safeDestination(string $destination, Request $request): string
    {
        $decoded = urldecode($destination);
        $parts = parse_url($decoded);

        if ($parts === false || (isset($parts['host']) && !hash_equals($request->getHost(), $parts['host']))) {
            return '/dashboard';
        }

        $path = '/'.ltrim((string) ($parts['path'] ?? 'dashboard'), '/');
        return $path.(isset($parts['query']) ? '?'.$parts['query'] : '');
    }
}
