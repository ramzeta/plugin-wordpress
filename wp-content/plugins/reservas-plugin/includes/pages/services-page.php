<?php
echo '<div class="wrap">';
echo '<h1>Gestión de Servicios</h1>';
echo '<p>Esta es una prueba para verificar si esta página se carga correctamente.</p>';

global $wpdb;
$services_table = $wpdb->prefix . 'services';
$services = $wpdb->get_results("SELECT * FROM $services_table ORDER BY created_at DESC");

echo '<form method="POST" id="new-service-form">';
echo '<h2>Nuevo Servicio</h2>';
echo '<label for="service_name">Nombre del Servicio</label>';
echo '<input type="text" id="service_name" name="service_name" required>';
echo '<label for="service_description">Descripción del Servicio</label>';
echo '<textarea id="service_description" name="service_description" required></textarea>';
echo '<label for="service_price">Precio del Servicio</label>';
echo '<input type="number" id="service_price" name="service_price" step="0.01" required>';
echo '<button type="submit">Añadir Servicio</button>';
echo '</form>';

if ($services) {
    echo '<h2>Servicios Existentes</h2>';
    echo '<table class="widefat fixed" cellspacing="0">';
    echo '<thead><tr><th>Nombre</th><th>Descripción</th><th>Precio</th><th>Acciones</th></tr></thead>';
    echo '<tbody>';
    foreach ($services as $service) {
        echo '<tr>';
        echo '<td>' . esc_html($service->service_name) . '</td>';
        echo '<td>' . esc_html($service->service_description) . '</td>';
        echo '<td>' . esc_html($service->service_price) . '</td>';
        echo '<td><button class="delete-service" data-service-id="' . esc_attr($service->id) . '">Eliminar</button></td>';
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
} else {
    echo '<p>No hay servicios registrados.</p>';
}
echo '</div>';
