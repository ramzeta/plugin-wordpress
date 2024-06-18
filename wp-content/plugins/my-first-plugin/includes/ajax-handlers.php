<?php

function dtp_update_task_dates() {
    global $wpdb;
    $tasks_table = $wpdb->prefix . 'daily_tasks';

    $task_id = intval($_POST['task_id']);
    $start_date = sanitize_text_field($_POST['start_date']);
    $end_date = sanitize_text_field($_POST['end_date']);

    $result = $wpdb->update($tasks_table, array(
        'start_date' => $start_date,
        'end_date' => $end_date,
    ), array('id' => $task_id));

    if ($result !== false) {
        wp_send_json_success();
    } else {
        wp_send_json_error();
    }
}
add_action('wp_ajax_update_task_dates', 'dtp_update_task_dates');
