require(['jquery'], function($) {
    var automationTable = $('#automation-table');
    var campaignId = automationTable.find('#campaign_id');
    var campaignIdEdit = automationTable.find('#campaign_id_edit');
    var cycleDay = automationTable.find('#cycle_day');
    var cycleDayEdit = automationTable.find('#cycle_day_edit');
    var grAutoresponder = automationTable.find('#gr_autoresponder');
    var autoresponders = JSON.parse($('#jsAutoresponders').val());
    
    $('.cycle_day').show();

    function populateSelectWithAutoresponders(cycleDay, campaign) {
        cycleDay.empty();

        var options = '';
        var campaignAutoresponders = autoresponders[campaign.val()];

        if (campaignAutoresponders === undefined) {
            campaignAutoresponders = autoresponders[campaign.attr('data-value')];
        }

        if (typeof campaignAutoresponders === 'object' && campaignAutoresponders.length > 0) {
            for (var i = 0; i < campaignAutoresponders.length; i++) {
                options += '<option value="' + campaignAutoresponders[i]['dayOfCycle']
                    + '">(Day: ' + campaignAutoresponders[i]['dayOfCycle'] + ') '
                    + campaignAutoresponders[i]['name']
                    + ' (Subject: ' + campaignAutoresponders[i]['subject'] + ')</option>';
            }
            cycleDay.prop('disabled', false);
            grAutoresponder.prop('disabled', false);
        } else {
            options = '<option value="">No autoresponders for chosen campaign!</option>';
            cycleDay.prop('disabled', true);
            grAutoresponder.prop('disabled', true).prop('checked', false);
        }

        cycleDay.append(options);
    }

    populateSelectWithAutoresponders(cycleDay, campaignId);

    campaignId.change(function () {
        populateSelectWithAutoresponders(cycleDay, campaignId);
    });

    campaignIdEdit.change(function () {
        populateSelectWithAutoresponders(cycleDayEdit, campaignIdEdit);
    });
});
