$(function () {
    var affiliates = [];
    var offers = [];
    var offerRequest = 0;
    var affiliateSelect = $('#affiliateSelect');
    var offerSelect = $('#offerSelect');
    var restoredOffer = offerSelect.attr('data-selected');

    $('#date').datetimepicker({dateFormat: 'yy-mm-dd', timeFormat: 'HH:mm:ss'});

    function updateSubmit() {
        $('#createSale').prop('disabled', !affiliateSelect.val() || !offerSelect.val());
    }

    function populate(select, items, search, placeholder, selected) {
        var filter = search.toLowerCase();
        select.empty().append(new Option(placeholder, ''));
        items.forEach(function (item) {
            if (String(item.name).toLowerCase().indexOf(filter) !== -1 || String(item.id).indexOf(filter) !== -1) {
                select.append(new Option(item.name + ' - ' + item.id, item.id));
            }
        });
        select.prop('disabled', items.length === 0);
        select.val(selected || '');
        updateSubmit();
    }

    function loadOffers() {
        var affiliate = affiliateSelect.val();
        var request = ++offerRequest;
        offers = [];
        $('#offerSearch').val('');
        $('#offerStatus').text('');
        populate(offerSelect, [], '', affiliate ? 'Loading offers...' : 'Select an affiliate first');
        if (!affiliate) {
            return;
        }

        $.getJSON('/sales/affiliate-offers/' + encodeURIComponent(affiliate)).done(function (data) {
            if (request !== offerRequest) return;
            offers = data;
            populate(offerSelect, offers, $('#offerSearch').val(), 'Select an offer', restoredOffer);
            restoredOffer = '';
            $('#offerStatus').text(offers.length ? '' : 'This affiliate has no active assigned offers.');
        }).fail(function () {
            if (request !== offerRequest) return;
            populate(offerSelect, [], '', 'Offers unavailable');
            $('#offerStatus').text('Unable to load offers. Select the affiliate again to retry.');
        });
    }

    affiliateSelect.on('change', function () {
        restoredOffer = '';
        loadOffers();
    });
    offerSelect.on('change', updateSubmit);
    $('#affiliateSearch').on('input', function () {
        var previous = affiliateSelect.val();
        populate(affiliateSelect, affiliates, this.value, 'Select an affiliate', previous);
        if (affiliateSelect.val() !== previous) loadOffers();
    });
    $('#offerSearch').on('input', function () {
        populate(offerSelect, offers, this.value, 'Select an offer', offerSelect.val());
    });
    $('#affiliateSearch, #offerSearch').on('keydown', function (event) {
        if (event.keyCode === 13) event.preventDefault();
    });
    $('#customPayoutCheckBox').on('change', function () {
        $('#customPayout').prop('disabled', !this.checked);
    });

    $.getJSON('/sales/affiliates').done(function (data) {
        affiliates = data;
        populate(affiliateSelect, affiliates, $('#affiliateSearch').val(), 'Select an affiliate', affiliateSelect.attr('data-selected'));
        $('#affiliateStatus').text(affiliates.length ? '' : 'No active affiliates are available.');
        if (affiliateSelect.val()) loadOffers();
    }).fail(function () {
        populate(affiliateSelect, [], '', 'Affiliates unavailable');
        $('#affiliateStatus').text('Unable to load affiliates. Reload the page to retry.');
    });
});
