require(['jquery'], function($) {
    var automationTable = $('#automation-table');
    var campaign_id = automationTable.find('#campaign_id');
    var campaign_id_edit = automationTable.find('#campaign_id_edit');
    var cycle_day = automationTable.find('#cycle_day');
    var cycle_day_edit = automationTable.find('#cycle_day_edit');
    var gr_autoresponder = automationTable.find('#gr_autoresponder');
    var autoresponders = JSON.parse($('#jsAutoresponders').val());
    
    //$('.cycle_day').show();

    function populateSelectWithAutoresponders(cycle_day, campaign) {
        cycle_day.empty();
        var options = '';
        var campaign_autoresponders = autoresponders[campaign.val()];
        if (campaign_autoresponders === undefined) {
            campaign_autoresponders = autoresponders[campaign.attr('data-value')];
        }
        if (typeof campaign_autoresponders === 'object' && campaign_autoresponders.length > 0) {
            for (var i = 0; i < campaign_autoresponders.length; i++) {
                options += '<option value="' + campaign_autoresponders[i]['dayOfCycle']
                    + '">(Day: ' + campaign_autoresponders[i]['dayOfCycle'] + ') '
                    + campaign_autoresponders[i]['name']
                    + ' (Subject: ' + campaign_autoresponders[i]['subject'] + ')</option>';
            }
            cycle_day.prop('disabled', false);
            gr_autoresponder.prop('disabled', false);
        } else {
            options = '<option value="">No autoresponders for chosen campaign!</option>';
            cycle_day.prop('disabled', true);
            gr_autoresponder.prop('disabled', true).prop('checked', false);
        }
        cycle_day.append(options);
    }

    populateSelectWithAutoresponders(cycle_day, campaign_id);

    campaign_id.change(function () {
        populateSelectWithAutoresponders(cycle_day, campaign_id);
    });

    campaign_id_edit.change(function () {
        populateSelectWithAutoresponders(cycle_day_edit, campaign_id_edit);
    });
});
