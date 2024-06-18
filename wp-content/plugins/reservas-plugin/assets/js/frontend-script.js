jQuery(document).ready(function($) {
    // Calendar initialization
    var today = new Date();
    var currentMonth = today.getMonth();
    var currentYear = today.getFullYear();

    var calendarTitle = $('#calendar-title');
    var calendarDays = $('#calendar-days');
    var selectedDateElement = $('#selected-date');

    function loadCalendar(month, year) {
        var firstDay = (new Date(year, month)).getDay();
        var daysInMonth = 32 - new Date(year, month, 32).getDate();
        
        calendarTitle.text(new Intl.DateTimeFormat('es-ES', { month: 'long', year: 'numeric' }).format(new Date(year, month)));

        calendarDays.empty();

        for (let i = 0; i < firstDay; i++) {
            calendarDays.append('<div class="empty"></div>');
        }

        for (let day = 1; day <= daysInMonth; day++) {
            calendarDays.append('<div class="day" data-date="' + year + '-' + (month + 1).toString().padStart(2, '0') + '-' + day.toString().padStart(2, '0') + '">' + day + '</div>');
        }

        $('.day').click(function() {
            var selectedDate = $(this).data('date');
            selectedDateElement.text('Fecha seleccionada: ' + selectedDate);
            $('#appointment_date').val(selectedDate);
            loadTimes(selectedDate);
        });
    }

    function loadTimes(selectedDate) {
        $.ajax({
            url: mapAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'get_available_times',
                date: selectedDate
            },
            success: function(response) {
                if (response.success) {
                    var options = '';
                    response.data.forEach(function(time) {
                        options += '<option value="' + time + '">' + time + '</option>';
                    });
                    $('#appointment_time').html(options);
                } else {
                    $('#appointment_time').html('<option value="">No hay horas disponibles</option>');
                }
            }
        });
    }

    $('#prev-month').click(function() {
        if (currentMonth === 0) {
            currentMonth = 11;
            currentYear--;
        } else {
            currentMonth--;
        }
        loadCalendar(currentMonth, currentYear);
    });

    $('#next-month').click(function() {
        if (currentMonth === 11) {
            currentMonth = 0;
            currentYear++;
        } else {
            currentMonth++;
        }
        loadCalendar(currentMonth, currentYear);
    });

    loadCalendar(currentMonth, currentYear);

    $('#appointment-form').submit(function(e) {
        e.preventDefault();

        var formData = {
            action: 'create_appointment',
            service_id: $('#service').val(),
            appointment_date: $('#appointment_date').val(),
            appointment_time: $('#appointment_time').val(),
            client_name: 'Google User', // Se puede obtener el nombre del usuario autenticado
            client_email: 'user@example.com' // Se puede obtener el email del usuario autenticado
        };

        $.post(mapAjax.ajax_url, formData, function(response) {
            if (response.success) {
                $('#form-message').text('Cita reservada con éxito.').css('color', 'green');
            } else {
                $('#form-message').text('Error al reservar la cita.').css('color', 'red');
            }
        });
    });
});
