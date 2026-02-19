<?php

namespace App\Http\Controllers;

use LeadMax\TrackYourStats\System\Session;
use PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException;
use PragmaRX\Google2FA\Exceptions\InvalidCharactersException;
use PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Str;

class TwoFactorController extends Controller
{
	public function enroll(Google2FA $google2fa)
	{
		$sessionUser = Session::user();
		abort_unless($sessionUser && $sessionUser->requiresTwoFactor(), 403);
		if ($sessionUser->two_factor_enabled && !session('2fa.passed')) {
			return redirect()->route('2fa.challenge');
		}

		$qrUrl = null;
		if ($sessionUser->two_factor_secret) {
			$qrUrl = $google2fa->getQRCodeUrl(
				config('app.name'),
				$sessionUser->email ?: (string) $sessionUser->user_name,
				$sessionUser->two_factor_secret
			);
		}

		return view('security.2fa-enroll', [
			'user' => $sessionUser,
			'qrUrl' => $qrUrl,
		]);
	}

	/**
	 * @throws IncompatibleWithGoogleAuthenticatorException
	 * @throws SecretKeyTooShortException
	 * @throws InvalidCharactersException
	 */
	public function startEnroll(Google2FA $google2fa)
	{
		$user = Session::user();
		abort_unless($user && $user->requiresTwoFactor(), 403);
		if ($user->two_factor_enabled && !session('2fa.passed')) {
			return redirect()->route('2fa.challenge');
		}

		$secret = $google2fa->generateSecretKey();

		$user->two_factor_secret = $secret;
		$user->two_factor_enabled = false;
		$user->two_factor_recovery_codes = $this->recoveryCodes();
		$user->two_factor_confirmed_at = null;
		$user->save();

		session(['2fa.required' => true, '2fa.passed' => false]);

		return redirect()->route('2fa.enroll');
	}

	/**
	 * @throws IncompatibleWithGoogleAuthenticatorException
	 * @throws SecretKeyTooShortException
	 * @throws InvalidCharactersException
	 */
	public function confirmEnroll(Google2FA $google2fa)
	{
		$user = Session::user();
		abort_unless($user && $user->requiresTwoFactor(), 403);

		request()->validate(['code' => ['required', 'digits:6']]);

		if (!$user->two_factor_secret) {
			return redirect()->route('2fa.enroll')->withErrors(['code' => 'Start enrollment first.']);
		}

		$valid = $google2fa->verifyKey($user->two_factor_secret, request('code'));

		if (!$valid) {
			return back()->withErrors(['code' => 'Invalid code.']);
		}

		$user->two_factor_enabled = true;
		$user->two_factor_confirmed_at = now();
		$user->save();

		session(['2fa.required' => true, '2fa.passed' => true]);

		$redirectUri = session()->pull('2fa.redirect_uri');
		if ($redirectUri) {
			return redirect($redirectUri);
		}

		return redirect('dashboard');
	}

	public function challenge()
	{
		$user = Session::user();
		abort_unless($user && $user->requiresTwoFactor(), 403);

		if (!$user->two_factor_enabled || !$user->two_factor_confirmed_at) {
			return redirect()->route('2fa.enroll');
		}

		if (session('2fa.passed')) {
			return redirect('dashboard');
		}

		return view('security.2fa-challenge');
	}

	/**
	 * @throws IncompatibleWithGoogleAuthenticatorException
	 * @throws SecretKeyTooShortException
	 * @throws InvalidCharactersException
	 */
	public function verifyChallenge(Google2FA $google2fa)
	{
		$user = Session::user();
		abort_unless($user && $user->requiresTwoFactor(), 403);

		request()->validate(['code' => ['required']]);
		$code = trim(request('code'));

		$validOtp = false;
		if ($user->two_factor_secret) {
			$validOtp = $google2fa->verifyKey($user->two_factor_secret, $code);
		}

		$validRecovery = false;
		$recovery = (array) $user->two_factor_recovery_codes;
		$normalizedCode = Str::upper($code);

		if (!$validOtp && in_array($normalizedCode, $recovery, true)) {
			$validRecovery = true;
			$user->two_factor_recovery_codes = array_values(array_diff($recovery, [$normalizedCode]));
			$user->save();
		}

		if (!$validOtp && !$validRecovery) {
			return back()->withErrors(['code' => 'Invalid code.']);
		}

		session(['2fa.passed' => true]);

		$redirectUri = session()->pull('2fa.redirect_uri');
		if ($redirectUri) {
			return redirect($redirectUri);
		}

		return redirect('dashboard');
	}

	private function recoveryCodes(): array
	{
		return collect(range(1, 8))
			->map(fn () => Str::upper(Str::random(10)))
			->all();
	}

}
