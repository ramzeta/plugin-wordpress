<?php

function dtp_enqueue_media() {
    wp_enqueue_media();
    wp_enqueue_script('dtp-admin-script', plugin_dir_url(__FILE__) . '../assets/js/daily-tasks-plugin-admin.js', array('jquery'), null, true);
    wp_enqueue_script('frappe-gantt', 'https://cdnjs.cloudflare.com/ajax/libs/frappe-gantt/0.5.0/frappe-gantt.min.js', array('jquery'), null, true);
    wp_enqueue_style('frappe-gantt-css', 'https://cdnjs.cloudflare.com/ajax/libs/frappe-gantt/0.5.0/frappe-gantt.css');
}
add_action('admin_enqueue_scripts', 'dtp_enqueue_media');

function dtp_enqueue_frontend_scripts() {
    wp_enqueue_script('frappe-gantt', 'https://cdnjs.cloudflare.com/ajax/libs/frappe-gantt/0.5.0/frappe-gantt.min.js', array('jquery'), null, true);
    wp_enqueue_style('frappe-gantt-css', 'https://cdnjs.cloudflare.com/ajax/libs/frappe-gantt/0.5.0/frappe-gantt.css');
    wp_enqueue_script('dtp-frontend-script', plugin_dir_url(__FILE__) . '../assets/js/daily-tasks-plugin-frontend.js', array('jquery', 'frappe-gantt'), null, true);
    wp_enqueue_style('dtp-frontend-css', plugin_dir_url(__FILE__) . '../assets/css/daily-tasks-plugin-frontend.css');

    // Pasar datos de tareas a JavaScript
    global $wpdb;
    $tasks_table = $wpdb->prefix . 'daily_tasks';
    $users_table = $wpdb->prefix . 'daily_users';
    $tasks = $wpdb->get_results("SELECT t.*, u.user_name FROM $tasks_table t LEFT JOIN $users_table u ON t.user_id = u.id ORDER BY t.created_at DESC");
    wp_localize_script('dtp-frontend-script', 'ganttTasksData', array('tasks' => $tasks, 'ajax_url' => admin_url('admin-ajax.php')));
}
add_action('wp_enqueue_scripts', 'dtp_enqueue_frontend_scripts');
