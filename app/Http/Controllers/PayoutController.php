<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Support\Facades\App;
use LeadMax\TrackYourStats\System\Session;
use Stripe\Account;
use Stripe\AccountLink;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;

class PayoutController extends Controller
{
    public function stripeOnboardRefresh(): \Illuminate\Foundation\Application|\Illuminate\Http\JsonResponse|\Illuminate\Routing\Redirector|\Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse {

		$user = Session::user();
		$accountId = $user->payoutData()->first()->payout_id;

	    if (App::environment() == 'production') {
		    $stripeSecret = env('STRIPE_SECRET');
	    } else {
		    $stripeSecret =  env('STRIPE_SANDBOX_SECRET');
	    }
	    Stripe::setApiKey($stripeSecret);

	    try {
		    $link = AccountLink::create( [
			    'account'     => $accountId,
			    'refresh_url' => route( 'stripe.refresh.url' ),
			    'return_url'  => route( 'stripe.complete' ),
			    'type'        => 'account_onboarding',
		    ] );

		    return redirect($link->url);

	    } catch (\Exception $e) {
		    LogDB($e, null);

		    return response()->json([
			    'status'  => 500,
			    'message' => $e->getMessage(),
		    ], 500);
	    }
    }

	public function stripeComplete(): \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse {

		$user = Session::user();
		$payoutData = $user->payoutData()->first();

		if (App::environment() == 'production') {
			$stripeSecret = env('STRIPE_SECRET');
		} else {
			$stripeSecret =  env('STRIPE_SANDBOX_SECRET');
		}
		Stripe::setApiKey($stripeSecret);

		try {
			$account = Account::retrieve($payoutData->payout_id);

			if ($account->details_submitted) {
				// Mark them as “Onboarding Complete”
				$payoutData->onboarding_complete = true;
				$payoutData->save();

				// Maybe show a success message
				return redirect()->route('payment.details')->with('success', 'Your Stripe account is set up!');
			} else {
				// Possibly let them know they still have steps to complete
				return redirect()->route( 'payment.details' )->with( 'error', 'Please finish onboarding.' );
			}

		} catch (\Exception $e) {
			LogDB($e, null);

			return response()->json([
				'status'  => 500,
				'message' => $e->getMessage(),
			], 500);
		}
	}

	/**
	 * @throws ApiErrorException
	 */
	public function stripeLogin(User $user) {

		$payoutDetails = $user->payoutData()->first();

		if (App::environment() == 'production') {
			$stripeSecret = env('STRIPE_SECRET');
		} else {
			$stripeSecret =  env('STRIPE_SANDBOX_SECRET');
		}
		Stripe::setApiKey($stripeSecret);

		$loginLink = Account::createLoginLink($payoutDetails->payout_id);

		return redirect($loginLink->url);
	}
}
