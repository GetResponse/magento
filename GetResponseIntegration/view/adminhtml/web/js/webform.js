require(['jquery'], function($) {
    var container = $('#container'),
        sod = container.find('#publish'),
        forms = container.find('.forms'),
        webformId = container.find('#webform_id'),
        webformUrl = container.find('#webform_url');

    if (sod.prop('checked') === true) {
        forms.removeClass('hidden');
    }

    sod.change(function () {
        forms.toggleClass('hidden');
    });

    webformUrl.val(webformId.find(':selected').attr('data-url'));

    webformId.change(function () {
        webformUrl.val(webformId.find(':selected').attr('data-url'));
    });
});
