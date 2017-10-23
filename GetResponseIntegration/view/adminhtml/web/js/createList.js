require(['jquery'], function($) {
    var LIST_NAME_LENGTH = 3;

    var createNewListForm = $('#createNewListForm');
    var listName = createNewListForm.find('#campaign_name');
    var from_field = createNewListForm.find('#from_field');
    var reply_to_field = createNewListForm.find('#reply_to_field');
    var confirmation_subject = createNewListForm.find('#confirmation_subject');
    var confirmation_body = createNewListForm.find('#confirmation_body');

    createNewListForm.submit(function () {
        return isFormValid();
    });

    function isFormValid() {
        validateIfEmptyField(listName);
        validateIfAtLeastLongField(listName, LIST_NAME_LENGTH);
        validateIfEmptyField(from_field);
        validateIfEmptyField(reply_to_field);
        validateIfEmptyField(confirmation_subject);
        validateIfEmptyField(confirmation_body);

        return !!validateIfEmptyField(listName) &&
            !!validateIfAtLeastLongField(listName, LIST_NAME_LENGTH) &&
            !!validateIfEmptyField(from_field) &&
            !!validateIfEmptyField(reply_to_field) &&
            !!validateIfEmptyField(confirmation_subject) &&
            !!validateIfEmptyField(confirmation_body);
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

    function validateIfAtLeastLongField(field, length) {
        if (validateIfEmptyField(field)) {
            if (field.val().length < length) {
                showAtLeastLongErrorMessage(field, length);
                return false;
            } else {
                clearErrorMessage(field);
                return true;
            }
        }
    }

    function showAtLeastLongErrorMessage(field, length) {
        var errorMessage = null;

        if (field.attr('id') === 'campaign_name') {
            errorMessage = 'You need to enter a name that\'s at least ' + length + ' characters long';
        }

        appendError(field, errorMessage);
    }

    function showEmptyErrorMessage(field) {
        var errorMessage = null;

        switch (field.attr('id')) {
            case 'campaign_name':
                errorMessage = 'You need to enter a name that\'s at least 3 characters long';
                break;
            case 'from_field':
                errorMessage = 'You need to select a sender email address';
                break;
            case 'reply_to_field':
                errorMessage = 'This is a required field';
                break;
            case 'confirmation_subject':
                errorMessage = 'You need to select a subject line for the subscription confirmation message';
                break;
            case 'confirmation_body':
                errorMessage = 'You need to select confirmation message body template';
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
});
