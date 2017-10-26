require(['jquery'], function($) {
    var sod = $('#gr_sync_order_data'),
        cfprow = $('#customNameFieldsRow'),
        campaignId = $('#campaign_id'),
        cycleDay = $('#cycle_day'),
        grAutoresponder = $('#gr_autoresponder'),
        autoresponders = JSON.parse($('#jsAutoresponders').val());

    if (sod.prop('checked') === true) {
        cfprow.removeClass('hidden');
    }

    sod.change(function () {
        cfprow.toggleClass('hidden');
    });

    function populateSelectWithAutoresponders() {
        cycle_day.empty();

        var options = '';
        var campaignAutoresponders = autoresponders[campaign_id.val()];

        if (typeof campaignAutoresponders == 'object' && campaignAutoresponders.length > 0) {
            for (var i = 0; i < campaignAutoresponders.length; i++) {
                options += '<option value="' + campaignAutoresponders[i]['dayOfCycle']
                    + '">(Day: ' + campaignAutoresponders[i]['dayOfCycle'] + ') '
                    + campaignAutoresponders[i]['name']
                    + ' (Subject: ' + campaignAutoresponders[i]['subject'] + ')</option>';
            }
            cycleDay.prop('disabled', false);
            grAutoresponder.prop('disabled', false);
        } else {
            options = '<option value="">no autoresponders</option>';
            cycleDay.prop('disabled', true);
            grAutoresponder.prop('disabled', true).prop('checked', false);
        }
        cycleDay.append(options);
    }

    populateSelectWithAutoresponders();

    campaignId.change(function () {
        populateSelectWithAutoresponders();
    });
});
