<?php

function map_enqueue_admin_scripts() {
    wp_enqueue_style('map-admin-style', plugin_dir_url(__FILE__) . '../assets/css/admin-style.css');
    wp_enqueue_script('map-admin-script', plugin_dir_url(__FILE__) . '../assets/js/admin-script.js', ['jquery'], null, true);
}
add_action('admin_enqueue_scripts', 'map_enqueue_admin_scripts');

function map_enqueue_frontend_scripts() {
    wp_enqueue_style('map-frontend-style', plugin_dir_url(__FILE__) . '../assets/css/frontend-style.css');
    wp_enqueue_script('map-frontend-script', plugin_dir_url(__FILE__) . '../assets/js/frontend-script.js', ['jquery'], null, true);

    // Pasar datos de Ajax a JavaScript
    wp_localize_script('map-frontend-script', 'mapAjax', [
        'ajax_url' => admin_url('admin-ajax.php'),
    ]);
}
add_action('wp_enqueue_scripts', 'map_enqueue_frontend_scripts');
