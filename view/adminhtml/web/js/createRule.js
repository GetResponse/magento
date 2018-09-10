require(['jquery'], function($) {
    var createRuleForm = $('#createRuleForm');
    var autoresponderDayField = createRuleForm.find('#field-autoresponder');
    var category = createRuleForm.find('#category');
    var action = createRuleForm.find('#action');
    var campaignId = createRuleForm.find('#campaign_id');
    var autoresponder = createRuleForm.find('#autoresponder');
    var grAutoresponder = createRuleForm.find('#gr_autoresponder');
    var autoresponders = JSON.parse($('#jsAutoresponders').val());

    if (grAutoresponder.prop('checked') === true) {
        autoresponderDayField.removeClass('hidden');
    }

    grAutoresponder.change(function () {
        autoresponderDayField.toggleClass('hidden');
    });

    createRuleForm.submit(function () {
        return isFormValid();
    });

    function isFormValid() {
        validateIfEmptyField(category);
        validateIfEmptyField(action);
        validateIfEmptyField(campaignId);

        return !!validateIfEmptyField(category) &&
            !!validateIfEmptyField(action) &&
            !!validateIfEmptyField(campaignId);
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
        autoresponder.empty();

        var options = '';
        var campaignAutoresponders = autoresponders[campaignId.val()];

        if (typeof campaignAutoresponders === 'object' && Object.keys(campaignAutoresponders).length > 0) {
            Object.keys(campaignAutoresponders).forEach(function(key) {
                var optionKey = campaignAutoresponders[key]['dayOfCycle'] + '_' + key;
                options += '<option value="' + optionKey + '" '
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

    campaignId.change(function() {
        populateSelectWithAutoresponders();
    });
});
