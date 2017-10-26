require(['jquery'], function($) {
    var CUSTOM_NUM = 3;

    var customs = JSON.parse($('#jsCustoms').val());
    var mappedCustomsNumber = $('.data-row').length;

    $(document).on('click', '.deleteCustomBtn', function (e) {
        e.preventDefault();

        removeCustomRow(this);
    });

    $('#addNewMappingBtn').on('click', function (e) {
        e.preventDefault();
        addCustomRow();
    });

    isNewMappingAvailable(mappedCustomsNumber);

    function removeCustomRow(row) {
        $(row).parent().parent().parent().remove();
        mappedCustomsNumber--;
        isNewMappingAvailable(mappedCustomsNumber);
    }

    function addCustomRow() {
        var isOddRow = mappedCustomsNumber % 2 === 0 ? '' : '_odd-row';
        var newCustom = '<tr class="data-row ' + isOddRow + '">' +
            '<td>' +
            '<div class="data-grid-cell-content">' +
            '<select name="custom[]">';
            customs.slice(0, CUSTOM_NUM).forEach(custom, index) {
                newCustom += '<option value="' + custom["customField"] + '">' + custom["customValue"] + '</option>';
            }
            newCustom += '</select>' +
            '</div>' +
            '</td>' +
            '<td>' +
            '<div class="data-grid-cell-content">' +
            '<input type="text" name="gr_custom[]" value="' + customs["customName"] + '">' +
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
        isNewMappingAvailable(mappedCustomsNumber);
    }

    function isNewMappingAvailable(mappedCustomsNumber) {
        if (customs.length === mappedCustomsNumber) {
            $('#addNewMappingBtn').attr('disabled', 'disabled');
        } else {
            $('#addNewMappingBtn').removeAttr('disabled');
        }
    }
});