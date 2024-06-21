jQuery(document).ready(function($) {
    // Manejo del formulario de selección de servicio
    $('#hsb-service-form').on('submit', function(e) {
        e.preventDefault();

        var selectedService = $('#service option:selected');
        var serviceId = selectedService.val();
        var duration = selectedService.data('duration');
        var price = selectedService.data('price');
        var serviceName = selectedService.text();

        $('#selected-service').val(serviceName);
        $('#selected-duration').val(duration);
        $('#selected-price').val(price);

        $('#hsb-service-selection').hide();
        $('#hsb-booking-container').show();
    });

    // Inicializar el calendario
    $('#hsb-calendar').fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
        },
        selectable: true,
        selectHelper: true,
        dayClick: function(date, jsEvent, view) {
            if (view.name === 'month') {
                $('#hsb-calendar').fullCalendar('changeView', 'agendaDay');
                $('#hsb-calendar').fullCalendar('gotoDate', date);
            }
        },
        eventRender: function(event, element, view) {
            if (view.name === 'month') {
                // No mostrar etiquetas en modo mes
                element.find('.fc-title').hide();
            } else {
                // Mostrar etiquetas en modos día y semana
                element.find('.fc-title').show();
            }
        },
        eventClick: function(event, jsEvent, view) {
            // Aquí puedes añadir cualquier lógica adicional que desees al hacer clic en una reserva.
            alert('Reserva: ' + event.title + '\nFecha: ' + event.start.format('YYYY-MM-DD HH:mm:ss'));
        },
        select: function(start, end) {
            // Comprobar disponibilidad antes de seleccionar
            var dateStr = moment(start).format('YYYY-MM-DD HH:mm:ss');
            var duration = $('#selected-duration').val();
            var endStr = moment(start).add(duration, 'minutes').format('YYYY-MM-DD HH:mm:ss');

            $.ajax({
                url: hsb_ajax_obj.ajax_url,
                method: 'POST',
                data: {
                    action: 'hsb_get_availability',
                    date: dateStr
                },
                success: function(response) {
                    var isAvailable = JSON.parse(response);
                    if (isAvailable) {
                        $('#date').val(dateStr + ' - ' + endStr);
                    } else {
                        alert('El horario seleccionado no está disponible.');
                    }
                }
            });
        },
        editable: true,
        eventLimit: true, // allow "more" link when too many events
        events: function(start, end, timezone, callback) {
            $.ajax({
                url: hsb_ajax_obj.ajax_url,
                dataType: 'json',
                data: {
                    action: 'hsb_get_bookings',
                    start: start.format(),
                    end: end.format()
                },
                success: function(data) {
                    var events = [];
                    $(data).each(function() {
                        events.push({
                            title: this.name + ' - ' + this.service,
                            start: this.date,
                            end: moment(this.date).add(this.duration, 'minutes').format('YYYY-MM-DD HH:mm:ss'), // using the duration from the database
                            allDay: false
                        });
                    });
                    callback(events);
                }
            });
        }
    });

    // Manejo del formulario de reserva
    $('#hsb-booking-form').on('submit', function(e) {
        e.preventDefault();

        var formData = {
            'action': 'hsb_submit_booking',
            'name': $('#name').val(),
            'email': $('#email').val(),
            'phone': $('#phone').val(),
            'date': $('#date').val(),
            'service': $('#selected-service').val(),
            'employee': $('#employee').val(),
            'price': $('#selected-price').val(),
            'duration': $('#selected-duration').val()
        };

        $.post(hsb_ajax_obj.ajax_url, formData, function(response) {
            $('#hsb-response').html(response);
            $('#hsb-calendar').fullCalendar('refetchEvents');
        });
    });
});
