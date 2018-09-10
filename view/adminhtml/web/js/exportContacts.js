require(['jquery'], function($) {
    var container = $('#container');
    var autoresponderDayField = container.find('#field-autoresponder');
    var sod = container.find('#gr_sync_order_data');
    var cfprow = container.find('#customNameFieldsRow');
    var campaignId = container.find('#campaign_id');
    var cycleDay = container.find('#cycle_day');
    var grAutoresponder = container.find('#gr_autoresponder');
    var autoresponders = JSON.parse($('#jsAutoresponders').val());
    var ecommerceCheckbox = $('#ecommerce');
    var storeBox = $('#store-box');

    if (sod.prop('checked') === true) {
        cfprow.removeClass('hidden');
    }

    sod.change(function () {
        cfprow.toggleClass('hidden');
    });

    ecommerceCheckbox.change(function() {
        storeBox.toggleClass('hidden');
    });

    if (grAutoresponder.prop('checked') === true) {
        autoresponderDayField.removeClass('hidden');
    }

    grAutoresponder.change(function () {
        autoresponderDayField.toggleClass('hidden');
    });

    function populateSelectWithAutoresponders() {
        cycleDay.empty();

        var options = '';
        var campaignAutoresponders = [];

        if (autoresponders) {
            campaignAutoresponders = autoresponders[campaignId.val()];
        }

        if (typeof campaignAutoresponders === 'object' && Object.keys(campaignAutoresponders).length > 0) {
            Object.keys(campaignAutoresponders).forEach(function(key) {
                options += '<option value="' + campaignAutoresponders[key]['dayOfCycle'] + '" '
                    + '>(Day: ' + campaignAutoresponders[key]['dayOfCycle'] + ') '
                    + campaignAutoresponders[key]['name']
                    + ' (Subject: ' + campaignAutoresponders[key]['subject'] + ')</option>';
            });
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
