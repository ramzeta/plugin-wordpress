<?php

function dtp_plugin_activate() {
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();
    $tasks_table = $wpdb->prefix . 'daily_tasks';
    $users_table = $wpdb->prefix . 'daily_users';
    $country_codes_table = $wpdb->prefix . 'country_codes';

    $sql = "
    CREATE TABLE $tasks_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        task_name tinytext NOT NULL,
        task_description text NOT NULL,
        task_image varchar(255) DEFAULT '' NOT NULL,
        start_date date DEFAULT NULL,
        end_date date DEFAULT NULL,
        is_completed tinyint(1) DEFAULT 0 NOT NULL,
        user_id mediumint(9) DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;

    CREATE TABLE $users_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_name tinytext NOT NULL,
        user_email varchar(100) NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;

    CREATE TABLE $country_codes_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        country_name varchar(255) NOT NULL,
        country_code varchar(10) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;
    ";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Insertar códigos de país iniciales
    $wpdb->insert($country_codes_table, array('country_name' => 'United States', 'country_code' => '1'));
    $wpdb->insert($country_codes_table, array('country_name' => 'United Kingdom', 'country_code' => '44'));
    $wpdb->insert($country_codes_table, array('country_name' => 'Spain', 'country_code' => '34'));
    // Añadir más códigos de país aquí según sea necesario
}
register_activation_hook(__FILE__, 'dtp_plugin_activate');
