require(['jquery'], function($) {
    var magentoCustomerAttributes = JSON.parse($('#jsMagentoCustomerAttributes').val());
    var getResponseCustomFields = JSON.parse($('#jsGetResponseCustomFields').val());
    var mappedCustomsNumber = $('.data-row').length;
    var addNewMappingBtn = $('#addNewMappingBtn');

    $(document).on('click', '.deleteCustomBtn', function (e) {
        e.preventDefault();
        removeCustomRow(this);
    });

    addNewMappingBtn.on('click', function (e) {
        e.preventDefault();
        addCustomRow();
    });

    function removeCustomRow(row) {
        $(row).parent().parent().parent().remove();
        mappedCustomsNumber--;
    }

    function addCustomRow() {
        var customerAttributeOptions = '';
        var getResponseCustomFieldsOptions = '';
        var oddRowClass = mappedCustomsNumber % 2 === 0 ? '' : '_odd-row';
        var newCustom = '';

        Object.keys(magentoCustomerAttributes).forEach(function(index) {
            if (magentoCustomerAttributes[index] !== null) {
                customerAttributeOptions += '<option value="' + index + '">' + magentoCustomerAttributes[index] + '</option>';
            }
        });

        getResponseCustomFields.forEach(function (getResponseCustomField) {
            getResponseCustomFieldsOptions += '<option value="' + getResponseCustomField['id'] + '">' + getResponseCustomField['name'] + '</option>';
        });

        newCustom = '<tr class="data-row ' + oddRowClass + '">' +
            '<td>' +
            '<div class="data-grid-cell-content">' +
            '<select name="custom[]">' +
            '<option value="">Select a Customer Attribute</option>' + customerAttributeOptions + '</select>' +
            '</div>' +
            '</td>' +
            '<td>' +
            '<div class="data-grid-cell-content">' +
            '<select name="gr_custom[]" class="getResponseCustomFieldSelect">' +
            '<option value="">Select a Custom Field</option>' + getResponseCustomFieldsOptions + '</select>' +
            '</div>' +
            '</td>' +
            '<td>' +
            '<div class="data-grid-cell-content">' +
            '<a href="#" class="deleteCustomBtn">Delete</a>' +
            '</div>' +
            '</td>' +
            '</tr>';

        $('#customsDataBody').append(newCustom);
        mappedCustomsNumber++;
    }

});
