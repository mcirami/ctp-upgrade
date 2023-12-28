<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LeadMax\TrackYourStats\Clicks\PostBackURLEventHandler;
use LeadMax\TrackYourStats\Clicks\URLEvents\ClickRegistrationEvent;
use LeadMax\TrackYourStats\System\Company;
use LeadMax\TrackYourStats\System\IPBlackList;
use LeadMax\TrackYourStats\System\Lander;

class IndexController extends Controller
{


    public function index(Request $request)
    {

        if ($request->get('repid') && $request->get('offerid')) {
            return $this->clickRegistration($request);
        }

        if ($request->get('uid')) {
            return $this->postBackRegistration($request);
        }

        // if its an offer url, and there wasn't any parameters for posting or generating clicks..
        if (Company::loadFromSession()->isCompanyOfferUrl($request->getHttpHost()) == true) {
            return redirect('404');
        }

        if (Company::getSub() != "chattrackpro") {

            $company = Company::loadFromSession();

            if ($request->getHttpHost() !== $company->landing_page && $request->getHttpHost() !== $company->login_url) {
                if (Company::getCustomSub() == "debug") {
                    return redirect('login.php');
                }
            }

            $lander = new Lander($company);
            $lander->loadCompanyLander();
        }

        return view('landing-page');
    }


    public function postBackRegistration(Request $request)
    {
        if ($request->get('uid')) {

            $blacklist = new IPBlackList($request->ip());
            if ($blacklist->isBlackListed()) {
                $blacklist->logIP();
            }

            if ($request->get("uid") !== Company::loadFromSession()->getUID()) {
                return JsonResponse::create(['status' => 404, 'message' => 'Unknown UID.'], 404);
            }

            try {
                $handler = new PostBackURLEventHandler();

                return $handler->handleRequest();
            } catch (\Exception $e) {
                LogDB($e, null);

                return JsonResponse::create([
                    'status'  => 500,
                    'message' => $e->getMessage(),
                ], 500);
            }

        }

    }


    public function clickRegistration(Request $request)
    {
        if ( ! $request->get('repid') && ! $request->get('offerid')) {
            return redirect('404')->setStatusCode('404');
        }
        $clickRegistrationEvent = new ClickRegistrationEvent($request->get('repid'), $request->get('offerid'),
            $request->query());
        if ( ! $clickRegistrationEvent->fire()) {
            return redirect('404');
        }
    }

}
