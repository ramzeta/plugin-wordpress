<?php

function dtp_plugin_menu() {
    add_menu_page('Daily Tasks Plugin', 'Tareas Diarias', 'manage_options', 'daily-tasks-plugin', 'dtp_plugin_page');
    add_submenu_page('daily-tasks-plugin', 'Usuarios', 'Usuarios', 'manage_options', 'daily-users-plugin', 'dtp_users_page');
    add_submenu_page('daily-tasks-plugin', 'Configuración de WhatsApp', 'WhatsApp', 'manage_options', 'whatsapp-settings', 'dtp_whatsapp_settings_page');
    add_submenu_page('daily-tasks-plugin', 'Prefijos de País', 'Prefijos de País', 'manage_options', 'country-codes-settings', 'dtp_country_codes_page');
}
add_action('admin_menu', 'dtp_plugin_menu');
