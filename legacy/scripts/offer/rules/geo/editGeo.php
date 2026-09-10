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
        "message" => "You do not have permission to edit rules.",
    ]);
    exit;
}

$isUpdate = isset($_POST["ruleData"]);
$ruleID = filter_input($isUpdate ? INPUT_POST : INPUT_GET, "ruleID", FILTER_VALIDATE_INT);

if (!$ruleID) {
    http_response_code(422);
    echo json_encode([
        "status" => "error",
        "message" => "A valid rule is required.",
    ]);
    exit;
}

try {
    $edit = new \LeadMax\TrackYourStats\Offer\Rules\Handlers\Geo($ruleID);

    if (!\LeadMax\TrackYourStats\Offer\RepHasOffer::noneRepOwnOffer(
        $edit->offerID,
        \LeadMax\TrackYourStats\System\Session::userID()
    )) {
        http_response_code(403);
        echo json_encode([
            "status" => "error",
            "message" => "You do not have access to this offer.",
        ]);
        exit;
    }

    if ($isUpdate) {
        $ruleData = json_decode($_POST["ruleData"]);
        $countryList = json_decode($_POST["data"] ?? "[]");

        if (!is_object($ruleData) || !is_array($countryList)) {
            throw new \RuntimeException("The rule data is invalid.");
        }

        $requiredFields = ["name", "redirectOffer", "deny", "is_active"];
        foreach ($requiredFields as $field) {
            if (!property_exists($ruleData, $field)) {
                throw new \RuntimeException("The rule data is incomplete.");
            }
        }

        $ruleData->ruleID = $ruleID;
        $updateScope = $_POST["updateScope"] ?? "shared";
        $edit->updateRule($ruleData, $countryList, $updateScope);

        $response = ["status" => "ok"];
        $shouldSavePredefinedRule = isset($_POST["saveAsPredefinedRule"])
            && (int)$_POST["saveAsPredefinedRule"] === 1;

        if ($shouldSavePredefinedRule) {
            try {
                \LeadMax\TrackYourStats\Offer\Rules\Handlers\PredefinedGeo::createFromRuleDataAndCountryList(
                    (int)\LeadMax\TrackYourStats\System\Session::userID(),
                    $_POST["predefinedRuleName"] ?? "",
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
        exit;
    }

    if (isset($_GET["getISOs"])) {
        $edit->dumpCountryCodes();
        exit;
    }

    if (isset($_GET["ruleInfo"])) {
        $edit->dumpRuleInfo();
        exit;
    }

    http_response_code(422);
    echo json_encode([
        "status" => "error",
        "message" => "A valid rule action is required.",
    ]);
} catch (\RuntimeException $e) {
    http_response_code(422);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage() ?: "Unable to update the geo rule.",
    ]);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Unable to update the geo rule.",
    ]);
}
