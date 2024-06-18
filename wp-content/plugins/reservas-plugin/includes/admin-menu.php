<?php

function map_plugin_menu() {
    add_menu_page('Appointments Plugin', 'Reservas', 'manage_options', 'appointments-plugin', 'map_admin_page');
    add_submenu_page('appointments-plugin', 'Servicios', 'Servicios', 'manage_options', 'services-plugin', 'map_services_page');
}
add_action('admin_menu', 'map_plugin_menu');
