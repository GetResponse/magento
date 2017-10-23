require(['jquery'], function($) {
    var sod = $('#gr_sync_order_data'), cfp = $('#customNameFields'),
        cfprow = $('#customNameFieldsRow');
    var campaign_id = $('#campaign_id'), cycle_day = $('#cycle_day'),
        gr_autoresponder = $('#gr_autoresponder');
    if (sod.prop('checked') === true) {
        cfprow.removeClass('hidden');
    }
    sod.change(function () {
        cfprow.toggleClass('hidden');
    });

	var autoresponders = JSON.parse($('#jsAutoresponders').val());

    function populateSelectWithAutoresponders() {
        cycle_day.empty();
        var options = '';
        var campaign_autoresponders = autoresponders[campaign_id.val()];
        if (typeof campaign_autoresponders == 'object' && campaign_autoresponders.length > 0) {
            for (var i = 0; i < campaign_autoresponders.length; i++) {
                options += '<option value="' + campaign_autoresponders[i]['dayOfCycle']
                    + '">(Day: ' + campaign_autoresponders[i]['dayOfCycle'] + ') '
                    + campaign_autoresponders[i]['name']
                    + ' (Subject: ' + campaign_autoresponders[i]['subject'] + ')</option>';
            }
            cycle_day.prop('disabled', false);
            gr_autoresponder.prop('disabled', false);
        } else {
            options = '<option value="">no autoresponders</option>';
            cycle_day.prop('disabled', true);
            gr_autoresponder.prop('disabled', true).prop('checked', false);
        }
        cycle_day.append(options);
    }

    populateSelectWithAutoresponders();

    campaign_id.change(function () {
        populateSelectWithAutoresponders();
    });
});
