require(['jquery'], function($) {
	var grsod = $('#gr_sync_order_data'),
        sod = $('#gr_enabled'),
        forms = $('.forms'),
        updateforms = $('.updateforms'),
        campaignId = $('#campaign_id'),
        cycleDay = $('#cycle_day'),
        grAutoresponder = $('#gr_autoresponder'),
        autoresponders = JSON.parse($('#jsAutoresponders').val());

    sod.change(function () {
        forms.toggleClass('hidden');

        if (grsod.prop('checked') === true) {
            updateforms.toggleClass('hidden');
        }

    });

    grsod.change(function () {
        updateforms.toggleClass('hidden');
    });

    if (grsod.prop('checked') === true) {
        updateforms.removeClass('hidden');
    }

    if (sod.prop('checked') === true) {
        forms.removeClass('hidden');
    }

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

    campaign_id.change(function () {
        populateSelectWithAutoresponders();
    });
});
