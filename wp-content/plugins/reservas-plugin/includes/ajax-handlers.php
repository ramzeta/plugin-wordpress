<?php

function map_create_appointment() {
    global $wpdb;
    $appointments_table = $wpdb->prefix . 'appointments';

    $service_id = intval($_POST['service_id']);
    $appointment_date = sanitize_text_field($_POST['appointment_date']);
    $appointment_time = sanitize_text_field($_POST['appointment_time']);
    $client_name = sanitize_text_field($_POST['client_name']);
    $client_email = sanitize_email($_POST['client_email']);
    $appointment_datetime = $appointment_date . ' ' . $appointment_time;

    // Validación y lógica de pago
    $payment_success = true; // Aquí deberías integrar la lógica real de pago (PayPal, Stripe, etc.)

    if ($payment_success) {
        // Guardar la cita en la base de datos después de confirmar el pago
        $wpdb->insert($appointments_table, [
            'service_id' => $service_id,
            'appointment_date' => $appointment_datetime,
            'client_name' => $client_name,
            'client_email' => $client_email,
        ]);

        wp_send_json_success();
    } else {
        wp_send_json_error('Error al procesar el pago.');
    }
}
add_action('wp_ajax_create_appointment', 'map_create_appointment');
add_action('wp_ajax_nopriv_create_appointment', 'map_create_appointment');

function map_get_available_times() {
    global $wpdb;
    $appointments_table = $wpdb->prefix . 'appointments';

    $selected_date = sanitize_text_field($_POST['date']);
    $times = [
        '09:00', '09:30', '10:00', '10:30', '11:00', '11:30',
        '12:00', '12:30', '13:00', '13:30', '14:00', '14:30',
        '15:00', '15:30', '16:00', '16:30', '17:00', '17:30',
        '18:00', '18:30', '19:00', '19:30', '20:00', '20:30'
    ];

    $booked_times = $wpdb->get_col($wpdb->prepare(
        "SELECT DATE_FORMAT(appointment_date, '%%H:%%i') FROM $appointments_table WHERE DATE(appointment_date) = %s",
        $selected_date
    ));

    $available_times = array_diff($times, $booked_times);

    if (!empty($available_times)) {
        wp_send_json_success($available_times);
    } else {
        wp_send_json_error('No hay horas disponibles');
    }
}
add_action('wp_ajax_get_available_times', 'map_get_available_times');
add_action('wp_ajax_nopriv_get_available_times', 'map_get_available_times');
