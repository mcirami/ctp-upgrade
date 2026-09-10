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
        "message" => "You do not have permission to delete rules.",
    ]);
    exit;
}

$ruleID = filter_input(INPUT_POST, "ruleID", FILTER_VALIDATE_INT);
$offerID = filter_input(INPUT_POST, "offerID", FILTER_VALIDATE_INT);

if (!$ruleID || !$offerID) {
    http_response_code(422);
    echo json_encode([
        "status" => "error",
        "message" => "A valid rule and offer are required.",
    ]);
    exit;
}

if (!\LeadMax\TrackYourStats\Offer\RepHasOffer::noneRepOwnOffer(
    $offerID,
    \LeadMax\TrackYourStats\System\Session::userID()
)) {
    http_response_code(403);
    echo json_encode([
        "status" => "error",
        "message" => "You do not have access to this offer.",
    ]);
    exit;
}

try {
    if (!\LeadMax\TrackYourStats\Offer\Rules::deleteRule($ruleID, $offerID)) {
        http_response_code(404);
        echo json_encode([
            "status" => "error",
            "message" => "The rule could not be found.",
        ]);
        exit;
    }

    echo json_encode(["status" => "ok"]);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Unable to delete the rule.",
    ]);
}
