require(['jquery'], function($) {
    var container = $('#container');
    var sod = container.find('#publish');
    var forms = container.find('.forms');
    var webformId = container.find('#webform_id');
    var webformUrl = container.find('#webform_url');

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
