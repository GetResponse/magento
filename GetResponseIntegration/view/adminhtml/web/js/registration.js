require(['jquery'], function($) {
	var grsod = $('#gr_sync_order_data'), sod = $('#gr_enabled'), forms = $('.forms'),
        updateforms = $('.updateforms');
    var campaign_id = $('#campaign_id'), cycle_day = $('#cycle_day'),
        gr_autoresponder = $('#gr_autoresponder');
    if (sod.prop('checked') === true) {
        forms.removeClass('hidden');
    }
    sod.change(function () {
        forms.toggleClass('hidden');

        if (grsod.prop('checked') === true) {
            updateforms.toggleClass('hidden');
        }

    });
    if (grsod.prop('checked') === true) {
        updateforms.removeClass('hidden');
    }
    grsod.change(function () {
        updateforms.toggleClass('hidden');
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
