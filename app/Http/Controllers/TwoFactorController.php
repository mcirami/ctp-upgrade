<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use LeadMax\TrackYourStats\System\Session;
use LeadMax\TrackYourStats\User\User;
use PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException;
use PragmaRX\Google2FA\Exceptions\InvalidCharactersException;
use PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Str;
class TwoFactorController extends Controller
{
	public function enroll()
	{
		$sessionUser = Session::user();
		/*$user = new User();
		abort_unless($user->is_loggedin() && $user->verify_login_session(), 403);*/
		abort_unless($sessionUser->requiresTwoFactor(), 403);

		return view('security.2fa-enroll', ['user' => $sessionUser]);
	}

	/**
	 * @throws IncompatibleWithGoogleAuthenticatorException
	 * @throws SecretKeyTooShortException
	 * @throws InvalidCharactersException
	 */
	public function startEnroll(Google2FA $google2fa)
	{
		$user = new User();
		abort_unless($user->requiresTwoFactor(), 403);

		$secret = $google2fa->generateSecretKey();

		// Save secret (encrypt it if you didn’t add model mutators)
		$user->two_factor_secret = $secret;
		$user->two_factor_enabled = false;
		$user->two_factor_recovery_codes = $this->recoveryCodes();
		$user->two_factor_confirmed_at = null;
		$user->save();

		return redirect()->route('2fa.enroll');
	}

	/**
	 * @throws IncompatibleWithGoogleAuthenticatorException
	 * @throws SecretKeyTooShortException
	 * @throws InvalidCharactersException
	 */
	public function confirmEnroll(Google2FA $google2fa)
	{
		$user = new User();
		abort_unless($user->requiresTwoFactor(), 403);

		request()->validate(['code' => ['required', 'digits:6']]);

		$valid = $google2fa->verifyKey($user->two_factor_secret, request('code'));

		if (!$valid) {
			return back()->withErrors(['code' => 'Invalid code.']);
		}

		$user->two_factor_enabled = true;
		$user->two_factor_confirmed_at = now();
		$user->save();

		session(['2fa.required' => true, '2fa.passed' => false]);

		return redirect()->route('2fa.challenge');
	}

	public function challenge()
	{
		$user = new User();
		abort_unless($user->is_loggedin() && $user->verify_login_session(), 403);
		abort_unless($user->requiresTwoFactor(), 403);

		return view('security.2fa-challenge');
	}

	/**
	 * @throws IncompatibleWithGoogleAuthenticatorException
	 * @throws SecretKeyTooShortException
	 * @throws InvalidCharactersException
	 */
	public function verifyChallenge(Google2FA $google2fa)
	{
		$user = new User();
		abort_unless($user->requiresTwoFactor(), 403);

		request()->validate(['code' => ['required']]);
		$code = trim(request('code'));

		$validOtp = $google2fa->verifyKey($user->two_factor_secret, $code);

		$validRecovery = false;
		$recovery = (array) $user->two_factor_recovery_codes;

		if (!$validOtp && in_array($code, $recovery, true)) {
			$validRecovery = true;
			$user->two_factor_recovery_codes = array_values(array_diff($recovery, [$code]));
			$user->save();
		}

		if (!$validOtp && !$validRecovery) {
			return back()->withErrors(['code' => 'Invalid code.']);
		}

		session(['2fa.passed' => true]);

		return redirect()->intended('dashboard');
	}

	private function recoveryCodes(): array
	{
		return collect(range(1, 8))
			->map(fn () => Str::upper(Str::random(10)))
			->all();
	}

}
