require(['jquery'], function($) {
    var container = $('#container');
    var autoresponderDayField = container.find('#field-autoresponder');
	var grsod = container.find('#gr_sync_order_data');
    var sod = container.find('#gr_enabled');
    var forms = container.find('.forms');
    var updateforms = container.find('.updateforms');
    var campaignId = container.find('#campaign_id');
    var cycleDay = container.find('#cycle_day');
    var grAutoresponder = container.find('#gr_autoresponder');
    var autoresponders = JSON.parse($('#jsAutoresponders').val());

    if (grAutoresponder.prop('checked') === true) {
        autoresponderDayField.removeClass('hidden');
    }

    grAutoresponder.change(function () {
        autoresponderDayField.toggleClass('hidden');
    });

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
        cycleDay.empty();

        var options = '';
        var campaignAutoresponders = autoresponders[campaignId.val()];

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
            autoresponderDayField.addClass('hidden');
        }

        cycleDay.append(options);
    }

    populateSelectWithAutoresponders();

    campaignId.change(function () {
        populateSelectWithAutoresponders();
    });
});
