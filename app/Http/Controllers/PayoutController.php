<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use LeadMax\TrackYourStats\System\Session;
use PHPMailer\PHPMailer\Exception;
use Stripe\Account;
use Stripe\AccountLink;

class PayoutController extends Controller
{
    public function stripeOnboardRefresh(): \Illuminate\Foundation\Application|\Illuminate\Http\JsonResponse|\Illuminate\Routing\Redirector|\Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse {

		$user = Session::user();
		$accountId = $user->payout_data()->first()->payout_id;

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
		$payoutData = $user->payout_data()->first();

		try {
			$account = Account::retrieve($payoutData->payout_id);

			if ($account->details_submitted) {
				// Mark them as “Onboarding Complete”
				$payoutData->onboarding_complete = true;
				$payoutData->save();

				// Maybe show a success message
				return redirect()->route('dashboard')->with('success', 'Your Stripe account is set up!');
			} else {
				// Possibly let them know they still have steps to complete
				return redirect()->route( 'dashboard' )->with( 'error', 'Please finish onboarding.' );
			}

		} catch (\Exception $e) {
			LogDB($e, null);

			return response()->json([
				'status'  => 500,
				'message' => $e->getMessage(),
			], 500);
		}


	}
}
