<?php

$user = new \LeadMax\TrackYourStats\User\User();
if (!$user->verify_login_session()) {
    send_to("login");
}

header("Content-Type: application/json");

$presetID = isset($_GET["presetID"]) ? (int) $_GET["presetID"] : 0;

if ($presetID <= 0) {
    http_response_code(422);
    echo json_encode([
        "message" => "A predefined rule was not selected."
    ]);
    exit;
}

$preset = \LeadMax\TrackYourStats\Offer\Rules\Handlers\PredefinedGeo::findForUser(
    $presetID,
    (int) \LeadMax\TrackYourStats\System\Session::userID()
);

if (!$preset) {
    http_response_code(404);
    echo json_encode([
        "message" => "Predefined rule not found."
    ]);
    exit;
}

echo json_encode($preset);
