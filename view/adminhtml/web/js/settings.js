require(['jquery'], function($) {
    var mxAccount = $('#is_mx'),
        mxOptions = $('#is_mx_options');
    
    if (mxAccount.prop('checked') === true) {
        mxOptions.toggleClass('hidden');
    }

    mxAccount.change(function () {
        mxOptions.toggleClass('hidden');
    });

    $('#disconnectBtn').on('click', openDisconnectConfirmationModal);

    function openDisconnectConfirmationModal(e) {
    	e.preventDefault();

        require(
            [
                'jquery',
                'Magento_Ui/js/modal/modal'
            ],
            function ($, modal) {
                var url = $('#jsDeleteUrl').val();
                var options = {
                    type: 'popup',
                    responsive: true,
                    innerScroll: true,
                    title: 'Disconnect from GetResponse?',
                    buttons: [
                        {
                            text: $.mage.__('Stay connected'),
                            class: 'action-secondary action-dismiss',
                            click: function () {
                                this.closeModal();
                            }
                        }, {
                            text: $.mage.__('Disconnect'),
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
        );
    }
});
