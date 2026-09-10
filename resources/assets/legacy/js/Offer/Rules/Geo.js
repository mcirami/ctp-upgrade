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
        $("#geoUpdateButton").off("click").on("click", function () {
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
        return typeof validateGeoRuleSubmission === "function"
            ? validateGeoRuleSubmission()
            : true;
    }

    setSubmissionState(isSubmitting) {
        if (typeof setGeoSubmissionState === "function") {
            setGeoSubmissionState(isSubmitting);
        }
    }

    buildUpdateRequest() {
        return {
            ruleData: {
                name: $("#geoRuleName").val(),
                ruleID: $("#geoRuleID").val(),
                redirectOffer: $("#geoRedirectOffer").val(),
                deny: document.getElementById("geoIsAllowed").checked,
                is_active: document.getElementById("geoIsActive").checked,
            },
            countryData: parseCountries("toAdd", true),
            predefinedRuleData: this.buildPredefinedRulePayload(),
        };
    }

    updateRule() {
        if (geoRequestInFlight) {
            return;
        }

        if (!this.validateSubmission()) {
            return;
        }

        var self = this;
        var request = this.buildUpdateRequest();

        promptGeoRuleUpdateDecision(function (updateScope) {
            self.submitUpdate(request, updateScope);
        });
    }

    submitUpdate(request, updateScope) {
        this.setSubmissionState(true);

        $.ajax({
            type: "POST",
            url: "/scripts/offer/rules/geo/editGeo.php",
            dataType: "json",
            data: {
                data: request.countryData,
                ruleData: JSON.stringify(request.ruleData),
                ruleID: request.ruleData.ruleID,
                updateScope: updateScope,
                saveAsPredefinedRule: request.predefinedRuleData.saveAsPredefinedRule,
                predefinedRuleName: request.predefinedRuleData.predefinedRuleName,
            },
            cache: false,
            traditional: true,
            success: function (result) {
                if (!result || (result.status !== "ok" && result.status !== "partial")) {
                    alert(result && result.message ? result.message : "Unable to update this rule.");
                    return;
                }

                if (result.status === "partial" && result.message) {
                    alert(result.message);
                }

                $("#geoModal").modal("hide");
                location.reload();
            },
            error: function (xhr) {
                var message = xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : "Unable to update this rule.";
                alert(message);
            },
            complete: function () {
                setGeoSubmissionState(false);
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
                var parsed = typeof result === "string" ? JSON.parse(result) : result;

                $("#geoRuleName").val(parsed.name);
                $("#geoOriginalRuleName").val(parsed.name);
                $("#geoPredefinedRuleName").val(parsed.name);
                $('#geoRedirectOffer option[value="' + parsed.redirectOffer + '"]').prop("selected", true);
                $("#geoIsAllowed").prop("checked", parseInt(parsed.deny, 10) === 1);
                $("#geoIsActive").prop("checked", parseInt(parsed.is_active, 10) === 1);
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
                var parsed = typeof result === "string" ? JSON.parse(result) : result;
                for (var i = 0; i < parsed.length; i++) {
                    addCountry(
                        parsed[i].country_code,
                        parseInt(parsed[i].cap_status, 10),
                        parsed[i].cap
                    );
                }
            },
        });
    }
}
