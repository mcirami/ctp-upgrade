<?php

namespace App\Http\Controllers;

use App\Privilege;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use LeadMax\TrackYourStats\Table\Paginate;

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

        $users = $users->get();

        return view('user.manage', compact('users'));
    }

	public function AuthRouteAPI(Request $request){
		return $request->user();
	}

	public function getUserSubIds() {
		$affId = $_GET["idrep"] ?? null;

		$subIds = DB::table('click_vars')
		            ->where('sub1', '!=', "")->distinct()
		            ->join('clicks', function($join) use($affId) {
			            $join->on('idclicks', '=', 'click_vars.click_id')->where('clicks.rep_idrep', '=', $affId);
		            })->select('click_vars.sub1')->pluck('sub1')->toArray();

		$blocked = DB::table('blocked_sub_ids')->where('rep_idrep', '=', $affId)->distinct()->pluck('sub_id')->toArray();

		$data = [];

		foreach($subIds as $subId) {
			if (in_array($subId, $blocked)) {
				$object = [
					'subId'     => $subId,
					'blocked'   => true
				];
			} else {
				$object = [
					'subId'     => $subId,
					'blocked'    => false
				];
			}

			array_push($data, $object);
		}

		return $data;

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
}
