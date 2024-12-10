<?php

namespace App\Http\Controllers;

use App\Click;
use App\Privilege;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use \LeadMax\TrackYourStats\System\Session;
use LeadMax\TrackYourStats\Table\Paginate;
use LeadMax\TrackYourStats\Table\Date;

class UserController extends Controller
{

    public function viewManagersAffiliates($id)
    {
        $manager = User::myUsers()->withRole(Privilege::ROLE_MANAGER)->findOrFail($id);


        $affiliates = $manager->users()->withRole(Privilege::ROLE_AFFILIATE)->with('referrer');

        $paginate = new Paginate(request('rpp',10), $affiliates->count());

        $affiliates = $affiliates->paginate(request('rpp', 10));

        return view('user.managers-affiliates', compact('manager', 'affiliates','paginate'));
    }

    public function viewManageUsers()
    {
        $this->validate(request(), [
            'showInactive' => 'numeric|min:0|max:1'
        ]);

        $users = User::myUsers()->withRole(request('role', Privilege::ROLE_AFFILIATE))->with('referrer');

        if (request('showInactive', 0) == 1) {
            $users->where('status', 0);
        } else {
            $users->where('status', 1);
        }

		if (Session::userType() == Privilege::ROLE_ADMIN && (request('role') == null ||  request('role') == '3')) {
			$userId = Session::userID();
			$managers = DB::table('rep')->where('referrer_repid', '=', $userId)->get()->pluck('idrep')->toArray();
			$users->whereIn('referrer_repid', $managers);
		}
		
        $users = $users->get();
		$users = $this->getDiffForHumans($users);

        return view('user.manage', compact('users'));
    }

	public function AuthRouteAPI(Request $request){
		return $request->user();
	}

	public function getUserSubIds() {
		$affId = $_GET["idrep"] ?? null;
		$date = new Date;
		$now = Carbon::now();
		$todaysDate = $date->convertDateTimezone($now);
		$subSixMonths = Carbon::now()->subMonths(1)->startOfDay();
		$sixMonthsAgo = $date->convertDateTimezone($subSixMonths);

		$blocked = DB::table('blocked_sub_ids')->where('rep_idrep', '=', $affId)->groupBy('rep_idrep')->pluck('sub_id')->toArray();
		$data = [];
		
		$subIds = DB::table('click_vars')
			->where('sub1', '!=', '')
			->join('clicks', function ($join) use ($affId, $sixMonthsAgo, $todaysDate) {
				$join->on('idclicks', '=', 'click_vars.click_id')
					->where('clicks.rep_idrep', '=', $affId)
					->whereBetween('first_timestamp', [$sixMonthsAgo, $todaysDate]);
			})
			->select('click_vars.sub1')
			->groupBy('click_vars.sub1') // Grouping instead of DISTINCT
			->orderBy('sub1')->lazy();
			/* ->pluck('sub1'); */
			
		foreach($subIds as $subId) {
			if ($subId->sub1 && in_array($subId->sub1, $blocked)) {
				$object = [
					'subId'     => $subId->sub1,
					'blocked'   => true
				];
			} elseif($subId) {
				$object = [
					'subId'     => $subId->sub1,
					'blocked'    => false
				];
			}

			array_push($data, $object);
		}

		return json_encode($data);

	}

	public function blockUserSubId(Request $request) {

		$userID = $request->user_id;
		$subID = $request->sub_id;

		DB::table('blocked_sub_ids')->insert([
			'rep_idrep' => $userID,
			'sub_id'    => $subID,
		]);

		return response()->json(['success' => true]);
	}

	public function unblockUserSubId(Request $request) {

		$userID = $request->user_id;
		$subID = $request->sub_id;

		DB::table('blocked_sub_ids')->where('rep_idrep', '=', $userID)->where('sub_id', '=', $subID)->delete();

		return response()->json(['success' => true]);
	}

	public function changeAffPayout(Request $request) {
		$message = null;

		$userID = $request->rep;
		$offer = $request->offer_id;
		$payout = $request->payout;

		// TODO: check if already has access or not.

		if(\LeadMax\TrackYourStats\System\Session::userType() != Privilege::ROLE_AFFILIATE) {

			$offerAccess = DB::table('rep_has_offer')
			                 ->where('rep_idrep', '=', $userID)
			                 ->where('offer_idoffer', '=', $offer)->get();
			if (count($offerAccess) > 0) {
				DB::table('rep_has_offer')
				  ->where('rep_idrep', '=', $userID)
				  ->where('offer_idoffer', '=', $offer)
				  ->update([
					  'payout' => $payout
				  ]);
				$success = true;
			} else {
				$success = false;
				$message = "User does not have access to offer yet!";
			}

		} else {
			$success = false;
			$message = "You don't have permissions to do this!";
		}
		return response()->json(['success' => $success, 'message' => $message]);
	}

	public function updateAffOfferAccess(Request $request) {
		$userID = $request->rep;
		$offer = $request->offer_id;
		$access = $request->access;
		$message = "";

		if(\LeadMax\TrackYourStats\System\Session::userType() != Privilege::ROLE_AFFILIATE) {

			if ($access) {
				DB::table('rep_has_offer')->insert([
					'rep_idrep'     => $userID,
					'offer_idoffer' => $offer,
					'payout'        => $request->payout
				]);
			} else {
				DB::table('rep_has_offer')
				  ->where('rep_idrep', '=', $userID)
				  ->where('offer_idoffer', '=', $offer)->delete();
			}

			$success = true;
		} else {
			$success = false;
			$message = "You don't have permissions to do this";
		}

		return response()->json(['success' => $success, 'message' => $message]);
	}

	public function editUserOffers(User $user) {
		$userID = $user->idrep;
		$userFName = $user->first_name;

		$offers = DB::table('offer')->where('status', '=', 1)->select('idoffer', 'offer_name', 'payout')->get()->toArray();

		foreach($offers as $index => $offer ) {
			$affHasOffer = DB::table('rep_has_offer')->where('rep_idrep', '=', $userID)->where('offer_idoffer', '=', $offer->idoffer)->get()->toArray();

			if (count($affHasOffer) > 0) {
				$offers[$index]->has_offer = true;
				$offers[$index]->reppayout = $affHasOffer[0]->payout;
			} else {
				$offers[$index]->has_offer = false;
				$offers[$index]->reppayout = 1.00;
			}
			$offers[$index]->idrep = $userID;
		}


		return view('user.offers')->with(['offers' => $offers, 'name' => $userFName]);
	}

	public function enableUserOfferCap(Request $request) {
		$userID = $request->rep;
		$offer = $request->offer_id;
		$status = $request->status;
		$message = "";

		if(\LeadMax\TrackYourStats\System\Session::userType() == Privilege::ROLE_GOD) {
			$userOfferCap = DB::table('user_offer_caps')->where("rep_idrep", $userID)->where('offer_idoffer', $offer)->first();

			if($userOfferCap) {
				DB::table('user_offer_caps')->where("rep_idrep", $userID)->where('offer_idoffer', $offer)->update( [
					'status' => $status
				] );

			} else {
				DB::table('user_offer_caps')->insert([
					'rep_idrep'     => $userID,
					'offer_idoffer' => $offer,
					'status'        => $status
				]);
			}

			$success = true;
		} else {
			$success = false;
			$message = "You don't have permissions to do this";
		}

		return response()->json(['success' => $success, 'message' => $message]);

	}

	public function setUserOfferCap(Request $request) {
		$userID = $request->rep;
		$offer = $request->offer_id;
		$cap = $request->cap;
		$message = "";
		if(\LeadMax\TrackYourStats\System\Session::userType() == Privilege::ROLE_GOD) {
			$userOfferCap = DB::table('user_offer_caps')->where("rep_idrep", $userID)->where('offer_idoffer', $offer)->first();
			if($userOfferCap) {
				DB::table('user_offer_caps')->where("rep_idrep", $userID)->where('offer_idoffer', $offer)->update( [
					'cap' => $cap
				] );

			} else {
				DB::table('user_offer_caps')->insert([
					'rep_idrep'     => $userID,
					'offer_idoffer' => $offer,
					'cap' => $cap
				]);
			}

			$success = true;

		} else {
			$success = false;
			$message = "You don't have permissions to do this";
		}

		return response()->json(['success' => $success, 'message' => $message]);
	}

	private function getDiffForHumans($users) {

		foreach($users as $key => $user) {
			if($user->rep_timestamp) {
				$user->rep_timestamp = Carbon::parse($user->rep_timestamp)->diffForHumans();
			}
		}

		return $users;
	}
}
