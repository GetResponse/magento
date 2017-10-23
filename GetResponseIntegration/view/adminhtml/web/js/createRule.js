require(['jquery'], function($) {
    var createRuleForm = $('#createRuleForm');
    var category = createRuleForm.find('#category');
    var action = createRuleForm.find('#action');
    var campaign_id = createRuleForm.find('#campaign_id');
    var cycle_day = $('#cycle_day');
    var gr_autoresponder = $('#gr_autoresponder');
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

    campaign_id.change(function() {
        populateSelectWithAutoresponders();
    });
});
