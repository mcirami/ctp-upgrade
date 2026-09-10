<?php

header("Content-Type: application/json");

$user = new \LeadMax\TrackYourStats\User\User();

if (!$user->verify_login_session()) {
    http_response_code(401);
    echo json_encode([
        "status" => "error",
        "message" => "Your session has expired. Please log in again.",
    ]);
    exit;
}

if (!\LeadMax\TrackYourStats\System\Session::permissions()->can("edit_offer_rules")) {
    http_response_code(403);
    echo json_encode([
        "status" => "error",
        "message" => "You do not have permission to create rules.",
    ]);
    exit;
}

$geoData = json_decode($_POST["data"] ?? "");

if (!is_array($geoData) || count($geoData) < 5) {
    http_response_code(422);
    echo json_encode([
        "status" => "error",
        "message" => "The geo rule data is invalid.",
    ]);
    exit;
}

$hasCountry = false;
foreach ($geoData as $item) {
    if (is_array($item) && count($item) >= 4) {
        $hasCountry = true;
        break;
    }
}

if (!$hasCountry) {
    http_response_code(422);
    echo json_encode([
        "status" => "error",
        "message" => "Add at least one country before saving a geo rule.",
    ]);
    exit;
}

try {
    $geo = new \LeadMax\TrackYourStats\Offer\Rules\Handlers\Geo($geoData);

    if (!\LeadMax\TrackYourStats\Offer\RepHasOffer::noneRepOwnOffer(
        $geo->offerID,
        \LeadMax\TrackYourStats\System\Session::userID()
    )) {
        http_response_code(403);
        echo json_encode([
            "status" => "error",
            "message" => "You do not have access to this offer.",
        ]);
        exit;
    }

    $geo->createRule();
    $response = ["status" => "ok"];

    $shouldSavePredefinedRule = isset($_POST["saveAsPredefinedRule"])
        && (int)$_POST["saveAsPredefinedRule"] === 1;

    if ($shouldSavePredefinedRule) {
        try {
            \LeadMax\TrackYourStats\Offer\Rules\Handlers\PredefinedGeo::createFromGeoPostData(
                (int)\LeadMax\TrackYourStats\System\Session::userID(),
                $_POST["predefinedRuleName"] ?? "",
                $geoData
            );
            $response["predefinedRuleSaved"] = true;
        } catch (\Throwable $e) {
            $response["status"] = "partial";
            $response["message"] = "Geo rule created, but the predefined rule could not be saved.";
        }
    }

    echo json_encode($response);
} catch (\InvalidArgumentException $e) {
    http_response_code(422);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage(),
    ]);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Unable to create the geo rule.",
    ]);
}
