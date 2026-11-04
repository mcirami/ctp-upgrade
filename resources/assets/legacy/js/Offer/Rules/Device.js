class deviceEdit {
    constructor(ruleID) {
        this.ruleID = String(ruleID);
    }

    loadRule() {
        $("#deviceRuleTitle").text("Edit Rule");
        $("#deviceRuleID").val(this.ruleID);
        $("#deviceCreateButton").hide();
        const update = $("#deviceUpdateButton").show().prop("disabled", true);
        update.off("click").on("click", () => this.updateRule());

        $.getJSON("/scripts/offer/rules/device/edit.php", {ruleID: this.ruleID, ruleInfo: 1})
            .done(rule => {
                if ($("#deviceRuleID").val() !== this.ruleID) return;
                rule.devices.forEach(addDevice);
                $("#deviceRuleName").val(rule.name);
                $("#deviceRedirectOffer").val(rule.redirectOffer);
                $("#deviceIsAllowed").prop("checked", Number(rule.deny) === 1);
                $("#deviceIsActive").prop("checked", Number(rule.is_active) === 1);
                $("#capIsActive").prop("checked", Number(rule.capStatus) === 1);
                $("#deviceCap").val(rule.capAmount);
                update.prop("disabled", false);
            })
            .fail(() => alert("Could not load the device rule."));
    }

    updateRule() {
        if (!hasSelectedDevices() || !confirm("Overwrite the existing device rule?")) return;
        const ruleData = {
            name: $("#deviceRuleName").val(),
            ruleID: this.ruleID,
            redirectOffer: $("#deviceRedirectOffer").val(),
            deny: $("#deviceIsAllowed").is(":checked"),
            is_active: $("#deviceIsActive").is(":checked"),
            capAmount: $("#deviceCap").val(),
            capStatus: $("#capIsActive").is(":checked")
        };
        $.post("/scripts/offer/rules/device/edit.php", {
            data: parseDevices("deviceToAdd", true),
            ruleData: JSON.stringify(ruleData)
        }).done(() => location.reload()).fail(xhr => alert(xhr.responseText || "Could not save the device rule."));
    }
}
