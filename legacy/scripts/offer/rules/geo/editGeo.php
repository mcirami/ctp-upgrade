<?php
/**
 * Created by PhpStorm.
 * User: dean
 * Date: 8/18/2017
 * Time: 12:07 PM
 */



//verify user session
$user = new \LeadMax\TrackYourStats\User\User();
if (!$user->verify_login_session() )
    send_to("login");


if(isset($_POST["ruleData"]))
{
    $ruleData = json_decode($_POST["ruleData"]); // rule data (rule ID, name, redirect_offer, etc)
    $countryList = json_decode($_POST["data"]); // (country list)


    $edit = new \LeadMax\TrackYourStats\Offer\Rules\Handlers\Geo($ruleData->ruleID);

    if(!\LeadMax\TrackYourStats\Offer\RepHasOffer::noneRepOwnOffer($edit->offerID, \LeadMax\TrackYourStats\System\Session::userID()))
        die("doesn't own offer");

    header("Content-Type: application/json");

    try {
        $updateScope = $_POST["updateScope"] ?? "shared";

        $edit->updateRule($ruleData, $countryList, $updateScope);

        $response = [
            "status" => "ok"
        ];

        $shouldSavePredefinedRule = isset($_POST["saveAsPredefinedRule"]) && (int) $_POST["saveAsPredefinedRule"] === 1;
        $predefinedRuleName = $_POST["predefinedRuleName"] ?? "";

        if ($shouldSavePredefinedRule) {
            try {
                \LeadMax\TrackYourStats\Offer\Rules\Handlers\PredefinedGeo::createFromRuleDataAndCountryList(
                    (int) \LeadMax\TrackYourStats\System\Session::userID(),
                    $predefinedRuleName,
                    $ruleData,
                    $countryList
                );

                $response["predefinedRuleSaved"] = true;
            } catch (\Throwable $e) {
                $response["status"] = "partial";
                $response["message"] = "Geo rule updated, but the predefined rule could not be saved.";
            }
        }

        echo json_encode($response);
    } catch (\Throwable $e) {
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => $e->getMessage() ?: "Unable to update geo rule."
        ]);
    }

    exit;
}


$edit = new \LeadMax\TrackYourStats\Offer\Rules\Handlers\Geo($_GET["ruleID"]);


if(!\LeadMax\TrackYourStats\Offer\RepHasOffer::noneRepOwnOffer($edit->offerID, \LeadMax\TrackYourStats\System\Session::userID()))
    die("doesn't own offer");



if(isset($_GET["getISOs"]))
    $edit->dumpCountryCodes();

if(isset($_GET["ruleInfo"]))
    $edit->dumpRuleInfo();
