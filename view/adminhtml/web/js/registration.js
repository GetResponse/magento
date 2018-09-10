require(['jquery'], function($) {
    var container = $('#container');
    var autoresponderDayField = container.find('#field-autoresponder');
	var grsod = container.find('#gr_sync_order_data');
    var sod = container.find('#gr_enabled');
    var forms = container.find('.forms');
    var updateforms = container.find('.updateforms');
    var campaignId = container.find('#campaign_id');
    var autoresponder = container.find('#autoresponder');
    var grAutoresponder = container.find('#gr_autoresponder');
    var settingsCycleDayKey = $('#jsSettingsCycleDayKey').val();
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

        if (grAutoresponder.prop('checked')) {
            autoresponderDayField.toggleClass('hidden');
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
        autoresponder.empty();

        var options = '';
        var campaignAutoresponders = autoresponders[campaignId.val()];

        if (typeof campaignAutoresponders === 'object' && Object.keys(campaignAutoresponders).length > 0) {
            Object.keys(campaignAutoresponders).forEach(function(key) {
                var optionKey = campaignAutoresponders[key]['dayOfCycle'] + '_' + key;
                options += '<option value="' + optionKey + '" '
                    + (settingsCycleDayKey === optionKey ? 'selected="selected"' : '')
                    + '>(Day: ' + campaignAutoresponders[key]['dayOfCycle'] + ') '
                    + campaignAutoresponders[key]['name']
                    + ' (Subject: ' + campaignAutoresponders[key]['subject'] + ')</option>';
            });
            autoresponder.prop('disabled', false);
            grAutoresponder.prop('disabled', false);
        } else {
            options = '<option value="">no autoresponders</option>';
            autoresponder.prop('disabled', true);
            grAutoresponder.prop('disabled', true).prop('checked', false);
            autoresponderDayField.addClass('hidden');
        }

        autoresponder.append(options);
    }

    populateSelectWithAutoresponders();

    campaignId.change(function () {
        populateSelectWithAutoresponders();
    });
});
