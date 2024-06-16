jQuery(document).ready(function ($) {
    $('#upload_image_button').click(function (e) {
        e.preventDefault();

        var image = wp.media({
            title: 'Subir Imagen',
            multiple: false
        }).open()
        .on('select', function () {
            var uploaded_image = image.state().get('selection').first();
            var image_url = uploaded_image.toJSON().url;
            $('#task_image').val(image_url);
            $('#task_image_preview').attr('src', image_url).show();
        });
    });
});
