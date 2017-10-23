require(['jquery'], function($) {
    var isAddingStore = false;
    var storesNumber = $('.data-row').length;
    var ecommerceStatusCheckbox = $('#e_commerce_status');
    var addNewShopRow = $('.addNewShopRow');

    ecommerceStatusCheckbox.change(function () {
        addNewShopRow.toggleClass('hidden');
    });

    $(document).on('click', '.deleteStoreBtn', function (e) {
        e.preventDefault();

        var shopId = $(this).attr('data-id');

        showDeleteStoreConfirm(shopId);
    });

    $('#addNewStoreBtn').on('click', function () {
        if (!isAddingStore) {
            addEditableStoreRow();
        }
    });

    $(document).on('click', '.confirmStoreBtn', function () {
        var shopName = $(this).parent().parent().parent().find('.storeName').val();

        $.ajax({
            type: "POST",
            url: $('#jsCreateShopUrl').val() + '?isAjax=true',
            data: {
                form_key: FORM_KEY,
                shop_name: shopName
            },
            success: function (data) {
                var json = jQuery.parseJSON(data);

                if (json.error) {
                    // tutaj trzeba dodać notyfikację dla klienta o błędzie!
                    return false;
                }

                removeEditStoreRow();
                addStoreRow(json);
            },
            error: function () {
                removeEditStoreRow();
            }
        })
    });

    function removeEditStoreRow() {
        isAddingStore = false;
        $(".data-row").last().remove();
        toggleDisableAddNewStoreBtn(isAddingStore);
    }

    function addStoreRow(data) {
        isAddingStore = false;
        var isOddRow = storesNumber % 2 === 0 ? '' : '_odd-row';
        var newStore = '<tr class="data-row ' + isOddRow + '">' +
            '<td>' +
            '<div class="data-grid-cell-content">' +
            data.name +
            '</div>' +
            '</td>' +
            '<td>' +
            '<div class="data-grid-cell-content">' +
            '<a href="#" data-id="' + data.shopId + '" class="deleteStoreBtn">Delete</a>' +
            '</div>' +
            '</td>' +
            '</tr>';

        $('#storesDataBody').append(newStore);
        updateStoreSelect(data);
        toggleDisableAddNewStoreBtn(isAddingStore);
    }

    function addEditableStoreRow() {
        isAddingStore = true;
        var isOddRow = storesNumber % 2 === 0 ? '' : '_odd-row';
        var newStore = '<tr class="data-row ' + isOddRow + '">' +
            '<td>' +
            '<div class="data-grid-cell-content">' +
            '<input type="text" class="storeName">' +
            '</div>' +
            '</td>' +
            '<td>' +
            '<div class="data-grid-cell-content">' +
            '<a href="#" class="confirmStoreBtn">Confirm</a>' +
            '</div>' +
            '</td>' +
            '</tr>';

        $('#storesDataBody').append(newStore);
        focusEditStoreNameInput();
        toggleDisableAddNewStoreBtn(isAddingStore);
    }

    function focusEditStoreNameInput() {
        $(".data-row").last().find('.storeName').focus();
    }

    function toggleDisableAddNewStoreBtn(isAddingStore) {
        if (isAddingStore) {
            $('#addNewStoreBtn').attr('disabled', 'disabled');
        } else {
            $('#addNewStoreBtn').removeAttr('disabled');
        }
    }

    function updateStoreSelect(data) {
        var newStoreOption = '<option value="' + data.shopId + '">' + data.name + '</option>';

        $('#shop_id').append(newStoreOption);
    }

    function showDeleteStoreConfirm(shopId) {
        require(
            ['jquery', 'Magento_Ui/js/modal/modal'],
            function ($, modal) {
                var url = $('#jsDeleteShopUrl').val() + '?id=' + shopId;
                var options = {
                    type: 'popup',
                    responsive: true,
                    innerScroll: true,
                    title: 'Delete store',
                    buttons: [
                        {
                            text: $.mage.__('Cancel'),
                            class: 'action-secondary action-dismiss',
                            click: function () {
                                this.closeModal();
                            }
                        }, {
                            text: $.mage.__('Delete'),
                            class: 'action-primary action-accept',
                            click: function () {
                                window.location.href = url;
                                return false;
                            }
                        },
                    ]
                };

                var popup = modal(options, $('#popup-modal'));
                $('#popup-modal').modal('openModal');
            }
        )
    }
});
