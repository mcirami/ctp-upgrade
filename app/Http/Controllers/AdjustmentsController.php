<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use LeadMax\TrackYourStats\Clicks\Click;
use LeadMax\TrackYourStats\Clicks\Conversion;
use LeadMax\TrackYourStats\Offer\AdjustmentsLog;
use LeadMax\TrackYourStats\System\Session;

class AdjustmentsController extends Controller
{


    public function showAddSaleLog()
    {
        return view('salelog.add');
    }

    public function getAffiliates()
    {
        return $this->affiliates()
            ->select('idrep as id', 'user_name as name')
            ->orderBy('name', 'ASC')
            ->get();
    }

    public function getAffiliatesOffers($id)
    {
        $user = $this->affiliates()->where('rep.idrep', '=', $id)->firstOrFail();


        return $user->offers()->select('idoffer as id', 'offer_name as name')->where('offer.status', '=',
            1)->orderBy('idoffer', 'DESC')->get();
    }

    private function affiliates()
    {
        return User::myUsers()
            ->whereHas('role', function ($query) {
                $query->where('is_rep', 1);
            })
            ->where('rep.status', 1);
    }

    public function createSale(Request $request)
    {

        $request->validate([
            'affiliate' => 'required|integer',
            'offer' => 'required|integer',
            'date' => 'required|date_format:Y-m-d H:i:s',
            'customPayout' => 'nullable|numeric|min:0',
        ]);




        $affiliate = $this->affiliates()->where('rep.idrep', $request->input('affiliate'))->first();
        if (!$affiliate) {
            throw ValidationException::withMessages(['affiliate' => 'Select an active affiliate you manage.']);
        }
        if (!$affiliate->offers()->where('offer.idoffer', $request->input('offer'))->where('offer.status', 1)->exists()) {
            throw ValidationException::withMessages(['offer' => 'Select an active offer assigned to this affiliate.']);
        }

        $click = new Click();
        $click->rep_idrep = $request->get('affiliate');
        $click->offer_idoffer = $request->get('offer');
        $click->first_timestamp = $request->get('date');
        $click->ip_address = $_SERVER["SERVER_ADDR"];
        $click->browser_agent = "TYS_GENERATED";
        $click->click_type = Click::TYPE_GENERATED;
        $click->save();


        $customPayout = $request->get('customPayout');
        $conversion = new Conversion();
        $conversion->timestamp = $request->date;
        $conversion->click_id = $click->id;

        if ($customPayout !== null) {
            $conversion->paid = $customPayout;
        }

        $conversion->registerSale();

        $log = new AdjustmentsLog($conversion->id, Session::userID());
        $log->setAction(AdjustmentsLog::ACTION_CREATE_SALE);
        $log->log();


        return redirect('/report/adjustments');
    }

}
