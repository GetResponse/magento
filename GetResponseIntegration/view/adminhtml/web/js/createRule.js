require(['jquery'], function($) {
    var createRuleForm = $('#createRuleForm');
    var category = createRuleForm.find('#category');
    var action = createRuleForm.find('#action');
    var campaignId = createRuleForm.find('#campaign_id');
    var cycleDay = createRuleForm.find('#cycle_day');
    var grAutoresponder = createRuleForm.find('#gr_autoresponder');
    var autoresponders = JSON.parse($('#jsAutoresponders').val());

    createRuleForm.submit(function () {
        return isFormValid();
    });

    function isFormValid() {
        validateIfEmptyField(category);
        validateIfEmptyField(action);
        validateIfEmptyField(campaign_id);

        return !!validateIfEmptyField(category) &&
            !!validateIfEmptyField(action) &&
            !!validateIfEmptyField(campaign_id);
    }

    function validateIfEmptyField(field) {
        if (field.val() === '') {
            showEmptyErrorMessage(field);
            return false;
        } else {
            clearErrorMessage(field);
            return true;
        }
    }

    function showEmptyErrorMessage(field) {
        var errorMessage = null;

        switch (field.attr('id')) {
            case 'category':
                errorMessage = 'You need to select your product category';
                break;
            case 'action':
                errorMessage = 'You need to select what to do with the customer';
                break;
            case 'campaign_id':
                errorMessage = 'You need to select a target list';
        }

        appendError(field, errorMessage);
    }

    function appendError(field, errorMessage) {
        field.next().html('<label class="admin__field-error">' + errorMessage + '</label>');
        field.parent().parent().addClass('_error');
    }

    function clearErrorMessage(field) {
        field.next().html('');
        field.parent().parent().removeClass('_error');
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

    campaignId.change(function() {
        populateSelectWithAutoresponders();
    });
});
