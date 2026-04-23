<?php
/**
 * Created by PhpStorm.
 * User: dean
 * Date: 8/18/2017
 * Time: 10:47 AM
 */


//verify user session
$user = new LeadMax\TrackYourStats\User\User();
if (!$user->verify_login_session())
{
	send_to("login");
}

$geoData = json_decode($_POST["data"]);

$geo = new \LeadMax\TrackYourStats\Offer\Rules\Handlers\Geo($geoData);

if (!\LeadMax\TrackYourStats\Offer\RepHasOffer::noneRepOwnOffer($geo->offerID, \LeadMax\TrackYourStats\System\Session::userID()))
{
	die("doesn't own offer");
}

header("Content-Type: application/json");

try {
	$geo->createRule();

	$response = [
		"status" => "ok"
	];

	$shouldSavePredefinedRule = isset($_POST["saveAsPredefinedRule"]) && (int) $_POST["saveAsPredefinedRule"] === 1;
	$predefinedRuleName = $_POST["predefinedRuleName"] ?? "";

	if ($shouldSavePredefinedRule) {
		try {
			\LeadMax\TrackYourStats\Offer\Rules\Handlers\PredefinedGeo::createFromGeoPostData(
				(int) \LeadMax\TrackYourStats\System\Session::userID(),
				$predefinedRuleName,
				$geoData
			);
			$response["predefinedRuleSaved"] = true;
		} catch (\Throwable $e) {
			$response["status"] = "partial";
			$response["message"] = "Geo rule created, but the predefined rule could not be saved.";
		}
	}

	echo json_encode($response);
} catch (\Throwable $e) {
	http_response_code(500);
	echo json_encode([
		"status" => "error",
		"message" => "Unable to create geo rule."
	]);
}




