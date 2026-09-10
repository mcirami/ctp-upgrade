<?php

header("Content-Type: application/json");

$user = new \LeadMax\TrackYourStats\User\User();

if (!$user->verify_login_session()) {
    http_response_code(401);
    echo json_encode(["message" => "Your session has expired. Please log in again."]);
    exit;
}

if (!\LeadMax\TrackYourStats\System\Session::permissions()->can("edit_offer_rules")) {
    http_response_code(403);
    echo json_encode(["message" => "You do not have permission to edit rules."]);
    exit;
}

$presetID = filter_input(INPUT_GET, "presetID", FILTER_VALIDATE_INT);

if (!$presetID) {
    http_response_code(422);
    echo json_encode(["message" => "A predefined rule was not selected."]);
    exit;
}

$preset = \LeadMax\TrackYourStats\Offer\Rules\Handlers\PredefinedGeo::findForUser(
    $presetID,
    (int)\LeadMax\TrackYourStats\System\Session::userID()
);

if (!$preset) {
    http_response_code(404);
    echo json_encode(["message" => "Predefined rule not found."]);
    exit;
}

echo json_encode($preset);
