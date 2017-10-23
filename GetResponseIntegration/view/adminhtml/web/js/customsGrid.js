require(['jquery'], function($) {
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
            '<select <?php if ($custom['default'] == 1):?>disabled<?php endif?> name="custom[]">' +
            '<?php foreach (array_slice($customs, $customNum) as $_custom):?>' +
            '<option <?php if ($_custom['id'] === $custom['id']):?>selected="selected"<?php endif?> value="<?php echo $_custom['custom_field']?>"><?php echo $_custom['custom_value']?></option>' +
            '<?php endforeach ?>' +
            '</select>' +
            '</div>' +
            '</td>' +
            '<td>' +
            '<div class="data-grid-cell-content">' +
            '<input type="text" name="gr_custom[]" value="<?php echo $custom['custom_name']?>">' +
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
