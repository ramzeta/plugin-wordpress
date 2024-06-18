<?php

function map_plugin_activate() {
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();
    $appointments_table = $wpdb->prefix . 'appointments';
    $services_table = $wpdb->prefix . 'services';

    $sql = "
    CREATE TABLE $appointments_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        service_id mediumint(9) NOT NULL,
        appointment_date datetime NOT NULL,
        client_name tinytext NOT NULL,
        client_email varchar(100) NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;

    CREATE TABLE $services_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        service_name tinytext NOT NULL,
        service_description text NOT NULL,
        service_price float NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;
    ";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'map_plugin_activate');
