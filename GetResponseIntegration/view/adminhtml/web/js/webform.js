require(['jquery'], function($) {
    var sod = $('#publish'), forms = $('.forms'), webform_id = $('#webform_id'),
        webform_url = $('#webform_url');
    if (sod.prop('checked') === true) {
        forms.removeClass('hidden');
    }
    sod.change(function () {
        forms.toggleClass('hidden');
    });
    webform_url.val(webform_id.find(':selected').attr('data-url'));
    webform_id.change(function () {
        webform_url.val(webform_id.find(':selected').attr('data-url'));
    });
});
