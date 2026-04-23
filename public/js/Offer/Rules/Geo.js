class geoEdit {
    constructor(ruleID) {
        this.ruleID = ruleID;
    }

    loadGeoRule() {
        if (typeof clearSelectedGeoCountries === "function") {
            clearSelectedGeoCountries();
        }

        if (typeof resetGeoPredefinedRuleForm === "function") {
            resetGeoPredefinedRuleForm("edit");
        }

        this.loadRuleCountries();
        this.getGeoRuleInfo();

        $("#geoRuleTitle").text("Edit Rule");
        $("#geoRuleID").val(this.ruleID);
        $("#geoCreateButton").hide();
        $("#geoUpdateButton").show();

        var self = this;
        $("#geoUpdateButton")
            .off("click")
            .on("click", function () {
                self.updateRule();
            });
    }

    buildPredefinedRulePayload() {
        if (typeof getGeoPredefinedRuleRequestData === "function") {
            return getGeoPredefinedRuleRequestData();
        }

        return {
            saveAsPredefinedRule: 0,
            predefinedRuleName: "",
        };
    }

    validateSubmission() {
        if (typeof validateGeoRuleSubmission === "function") {
            return validateGeoRuleSubmission();
        }

        return true;
    }

    setSubmissionState(isSubmitting) {
        if (typeof setGeoSubmissionState === "function") {
            setGeoSubmissionState(isSubmitting);
        }
    }

    updateRule() {
        if (
            typeof geoRequestInFlight !== "undefined" &&
            geoRequestInFlight === true
        ) {
            return;
        }

        if (!this.validateSubmission()) {
            return;
        }

        var ruleData = {
            name: $("#geoRuleName").val(),
            ruleID: $("#geoRuleID").val(),
            redirectOffer: $("#geoRedirectOffer").val(),
            deny: document.getElementById("geoIsAllowed").checked,
            is_active: document.getElementById("geoIsActive").checked,
        };
        var predefinedRuleData = this.buildPredefinedRulePayload();
        var self = this;

        this.setSubmissionState(true);

        $.ajax({
            type: "POST",
            url: "/scripts/offer/rules/geo/editGeo.php",
            dataType: "json",
            data: {
                data: parseCountries("toAdd", true),
                ruleData: JSON.stringify(ruleData),
                ruleID: ruleData["ruleID"],
                saveAsPredefinedRule: predefinedRuleData.saveAsPredefinedRule,
                predefinedRuleName: predefinedRuleData.predefinedRuleName,
            },
            cache: false,
            traditional: true,
            success: function (result) {
                if (result && result["status"] === "error") {
                    alert(result["message"] || "Unable to update geo rule.");
                    return;
                }

                if (
                    result &&
                    result["status"] === "partial" &&
                    result["message"]
                ) {
                    alert(result["message"]);
                }

                $("#geoModal").modal("hide");
                location.reload();
            },
            error: function (result) {
                alert(
                    (result.responseJSON && result.responseJSON.message) ||
                        result.responseText ||
                        "Unable to update geo rule."
                );
            },
            complete: function () {
                self.setSubmissionState(false);
            },
        });
    }

    getGeoRuleInfo() {
        $.ajax({
            type: "GET",
            url: "/scripts/offer/rules/geo/editGeo.php",
            data: "&ruleID=" + this.ruleID + "&ruleInfo=1",
            cache: false,

            success: function (result) {
                var parsed = JSON.parse(result);
                var denyIsActive =
                    parseInt(parsed["deny"], 10) === 1 ||
                    parsed["deny"] === true;
                var ruleIsActive =
                    parseInt(parsed["is_active"], 10) === 1 ||
                    parsed["is_active"] === true;

                $("#geoRuleName").val(parsed["name"]);

                $(
                    '#geoRedirectOffer option[value="' +
                        parsed["redirectOffer"] +
                        '"]'
                ).prop("selected", true);

                $("#geoIsAllowed").prop("checked", denyIsActive);
                $("#geoIsActive").prop("checked", ruleIsActive);
            },
        });
    }

    loadRuleCountries() {
        $.ajax({
            type: "GET",
            url: "/scripts/offer/rules/geo/editGeo.php",
            data: "&ruleID=" + this.ruleID + "&getISOs=1",
            cache: false,

            success: function (result) {
                var parsed = JSON.parse(result);
                for (var i = 0; i < parsed.length; i++) {
                    addCountry(
                        parsed[i].country_code,
                        parseInt(parsed[i].cap_status, 10) || 0,
                        parseInt(parsed[i].cap, 10) || 0
                    );
                }
            },
        });
    }
}
