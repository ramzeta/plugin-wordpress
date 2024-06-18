<?php
global $wpdb;
$appointments_table = $wpdb->prefix . 'appointments';
$appointments = $wpdb->get_results("SELECT * FROM $appointments_table ORDER BY appointment_date DESC");

echo '<div class="wrap">';
echo '<h1>Gestión de Citas</h1>';
echo '<table class="widefat fixed" cellspacing="0">';
echo '<thead><tr><th>Servicio</th><th>Fecha de la cita</th><th>Nombre del cliente</th><th>Email del cliente</th><th>Fecha de creación</th></tr></thead>';
echo '<tbody>';
foreach ($appointments as $appointment) {
    echo '<tr>';
    echo '<td>' . esc_html($appointment->service_id) . '</td>';
    echo '<td>' . esc_html($appointment->appointment_date) . '</td>';
    echo '<td>' . esc_html($appointment->client_name) . '</td>';
    echo '<td>' . esc_html($appointment->client_email) . '</td>';
    echo '<td>' . esc_html($appointment->created_at) . '</td>';
    echo '</tr>';
}
echo '</tbody>';
echo '</table>';
echo '</div>';
