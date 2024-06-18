jQuery(document).ready(function($) {
    $('#new-service-form').submit(function(e) {
        e.preventDefault();

        var formData = {
            action: 'create_service',
            service_name: $('#service_name').val(),
            service_description: $('#service_description').val(),
            service_price: $('#service_price').val(),
        };

        $.post(ajaxurl, formData, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Error al crear el servicio.');
            }
        });
    });

    $('.delete-service').click(function() {
        if (confirm('¿Seguro que deseas eliminar este servicio?')) {
            var serviceId = $(this).data('service-id');

            var formData = {
                action: 'delete_service',
                service_id: serviceId,
            };

            $.post(ajaxurl, formData, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error al eliminar el servicio.');
                }
            });
        }
    });
});
