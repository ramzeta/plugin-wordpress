<?php
/*
Plugin Name: Daily Tasks Plugin
Description: Un plugin para gestionar tareas diarias con imágenes, usuarios y diagrama de Gantt.
Version: 1.3
Author: Your Name
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Crear las tablas necesarias al activar el plugin
function dtp_plugin_activate() {
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();
    $tasks_table = $wpdb->prefix . 'daily_tasks';
    $users_table = $wpdb->prefix . 'daily_users';

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
    ";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'dtp_plugin_activate');

// Crear una página de administración para gestionar tareas y usuarios
function dtp_plugin_menu() {
    add_menu_page('Daily Tasks Plugin', 'Tareas Diarias', 'manage_options', 'daily-tasks-plugin', 'dtp_plugin_page');
    add_submenu_page('daily-tasks-plugin', 'Usuarios', 'Usuarios', 'manage_options', 'daily-users-plugin', 'dtp_users_page');
}
add_action('admin_menu', 'dtp_plugin_menu');

// Enqueue Media Library scripts and custom JS
function dtp_enqueue_media() {
    wp_enqueue_media();
    wp_enqueue_script('dtp-admin-script', plugin_dir_url(__FILE__) . 'daily-tasks-plugin-admin.js', array('jquery'), null, true);
    wp_enqueue_script('frappe-gantt', 'https://cdnjs.cloudflare.com/ajax/libs/frappe-gantt/0.5.0/frappe-gantt.min.js', array('jquery'), null, true);
    wp_enqueue_style('frappe-gantt-css', 'https://cdnjs.cloudflare.com/ajax/libs/frappe-gantt/0.5.0/frappe-gantt.css');
}
add_action('admin_enqueue_scripts', 'dtp_enqueue_media');

// Enqueue Gantt Chart scripts for frontend
function dtp_enqueue_frontend_scripts() {
    wp_enqueue_script('frappe-gantt', 'https://cdnjs.cloudflare.com/ajax/libs/frappe-gantt/0.5.0/frappe-gantt.min.js', array('jquery'), null, true);
    wp_enqueue_style('frappe-gantt-css', 'https://cdnjs.cloudflare.com/ajax/libs/frappe-gantt/0.5.0/frappe-gantt.css');
    wp_enqueue_script('dtp-frontend-script', plugin_dir_url(__FILE__) . 'daily-tasks-plugin-frontend.js', array('jquery', 'frappe-gantt'), null, true);

    // Pasar datos de tareas a JavaScript
    global $wpdb;
    $tasks_table = $wpdb->prefix . 'daily_tasks';
    $users_table = $wpdb->prefix . 'daily_users';
    $tasks = $wpdb->get_results("SELECT t.*, u.user_name FROM $tasks_table t LEFT JOIN $users_table u ON t.user_id = u.id ORDER BY t.created_at DESC");
    wp_localize_script('dtp-frontend-script', 'ganttTasksData', array('tasks' => $tasks));
}
add_action('wp_enqueue_scripts', 'dtp_enqueue_frontend_scripts');

// Página de administración para gestionar tareas
function dtp_plugin_page() {
    global $wpdb;
    $tasks_table = $wpdb->prefix . 'daily_tasks';
    $users_table = $wpdb->prefix . 'daily_users';

    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['new_task'])) {
            $task_name = sanitize_text_field($_POST['task_name']);
            $task_description = sanitize_textarea_field($_POST['task_description']);
            $task_image = esc_url_raw($_POST['task_image']);
            $start_date = sanitize_text_field($_POST['start_date']);
            $end_date = sanitize_text_field($_POST['end_date']);
            $user_id = intval($_POST['user_id']);

            $wpdb->insert($tasks_table, [
                'task_name' => $task_name,
                'task_description' => $task_description,
                'task_image' => $task_image,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'user_id' => $user_id
            ]);
        } elseif (isset($_POST['edit_task'])) {
            $task_id = intval($_POST['task_id']);
            $task_name = sanitize_text_field($_POST['task_name']);
            $task_description = sanitize_textarea_field($_POST['task_description']);
            $task_image = esc_url_raw($_POST['task_image']);
            $start_date = sanitize_text_field($_POST['start_date']);
            $end_date = sanitize_text_field($_POST['end_date']);
            $user_id = intval($_POST['user_id']);

            $wpdb->update($tasks_table, [
                'task_name' => $task_name,
                'task_description' => $task_description,
                'task_image' => $task_image,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'user_id' => $user_id
            ], ['id' => $task_id]);
        } elseif (isset($_POST['delete_task'])) {
            $task_id = intval($_POST['task_id']);
            $wpdb->delete($tasks_table, ['id' => $task_id]);
        } elseif (isset($_POST['complete_task'])) {
            $task_id = intval($_POST['task_id']);
            $wpdb->update($tasks_table, ['is_completed' => 1], ['id' => $task_id]);
        }
    }

    // Fetch tasks and users
    $tasks = $wpdb->get_results("SELECT t.*, u.user_name FROM $tasks_table t LEFT JOIN $users_table u ON t.user_id = u.id ORDER BY t.created_at DESC");
    $users = $wpdb->get_results("SELECT * FROM $users_table ORDER BY created_at DESC");

    echo '<div class="wrap">';
    echo '<h1>Gestión de Tareas Diarias</h1>';

    echo '<h2>Nueva Tarea</h2>';
    echo '<form method="POST">';
    echo '<table class="form-table"><tbody>';
    echo '<tr><th><label for="task_name">Nombre de la Tarea</label></th><td><input name="task_name" type="text" id="task_name" value="" class="regular-text"></td></tr>';
    echo '<tr><th><label for="task_description">Descripción de la Tarea</label></th><td><textarea name="task_description" id="task_description" class="large-text"></textarea></td></tr>';
    echo '<tr><th><label for="task_image">Imagen de la Tarea</label></th><td><input type="hidden" name="task_image" id="task_image" value="" class="regular-text"><button type="button" class="button" id="upload_image_button">Subir Imagen</button><br><img id="task_image_preview" src="" style="max-width: 150px; margin-top: 10px; display: none;"></td></tr>';
    echo '<tr><th><label for="start_date">Fecha de Inicio</label></th><td><input type="date" name="start_date" id="start_date" class="regular-text"></td></tr>';
    echo '<tr><th><label for="end_date">Fecha de Fin</label></th><td><input type="date" name="end_date" id="end_date" class="regular-text"></td></tr>';
    echo '<tr><th><label for="user_id">Asignar a Usuario</label></th><td><select name="user_id" id="user_id" class="regular-text">';
    foreach ($users as $user) {
        echo '<option value="' . esc_attr($user->id) . '">' . esc_html($user->user_name) . '</option>';
    }
    echo '</select></td></tr>';
    echo '</tbody></table>';
    echo '<p class="submit"><input type="submit" name="new_task" id="submit" class="button button-primary" value="Crear Tarea"></p>';
    echo '</form>';

    echo '<h2>Tareas Existentes</h2>';
    if ($tasks) {
        echo '<table class="widefat fixed" cellspacing="0">';
        echo '<thead><tr><th>Nombre de la Tarea</th><th>Descripción</th><th>Imagen</th><th>Fecha de Inicio</th><th>Fecha de Fin</th><th>Asignada a</th><th>Fecha de Creación</th><th>Estado</th><th>Acciones</th></tr></thead>';
        echo '<tbody>';
        foreach ($tasks as $task) {
            echo '<tr>';
            echo '<td>' . esc_html($task->task_name) . '</td>';
            echo '<td>' . esc_html($task->task_description) . '</td>';
            echo '<td>';
            if (!empty($task->task_image)) {
                echo '<img src="' . esc_url($task->task_image) . '" style="max-width: 100px;">';
            }
            echo '</td>';
            echo '<td>' . esc_html($task->start_date) . '</td>';
            echo '<td>' . esc_html($task->end_date) . '</td>';
            echo '<td>' . esc_html($task->user_name) . '</td>';
            echo '<td>' . esc_html($task->created_at) . '</td>';
            echo '<td>' . ($task->is_completed ? 'Completada' : 'Pendiente') . '</td>';
            echo '<td>';
            if (!$task->is_completed) {
                echo '<form method="POST" style="display:inline;"><input type="hidden" name="task_id" value="' . intval($task->id) . '">';
                echo '<input type="submit" name="complete_task" class="button" value="Completar"></form> ';
                echo '<button class="button edit-task" data-task-id="' . intval($task->id) . '">Editar</button> ';
            }
            echo '<form method="POST" style="display:inline;"><input type="hidden" name="task_id" value="' . intval($task->id) . '"><input type="submit" name="delete_task" class="button" value="Eliminar"></form>';
            echo '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
    } else {
        echo '<p>No hay tareas registradas.</p>';
    }

    echo '</div>';

    // JavaScript para manejar la edición de tareas
    echo '<script type="text/javascript">
        jQuery(document).ready(function($) {
            $(".edit-task").click(function() {
                var taskId = $(this).data("task-id");
                var row = $(this).closest("tr");

                var taskName = row.find("td:eq(0)").text().trim();
                var taskDescription = row.find("td:eq(1)").text().trim();
                var taskImage = row.find("td:eq(2) img").attr("src");
                var startDate = row.find("td:eq(3)").text().trim();
                var endDate = row.find("td:eq(4)").text().trim();
                var userId = row.find("td:eq(5)").data("user-id");

                $("#task_name").val(taskName);
                $("#task_description").val(taskDescription);
                $("#task_image").val(taskImage);
                $("#task_image_preview").attr("src", taskImage).show();
                $("#start_date").val(startDate);
                $("#end_date").val(endDate);
                $("#user_id").val(userId);

                $("#submit").attr("name", "edit_task").val("Guardar Cambios");
                $("<input>").attr({
                    type: "hidden",
                    name: "task_id",
                    value: taskId
                }).appendTo("form");

                $("html, body").animate({ scrollTop: 0 }, "slow");
            });
        });
    </script>';
}

// Página de administración para gestionar usuarios
function dtp_users_page() {
    global $wpdb;
    $users_table = $wpdb->prefix . 'daily_users';

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['new_user'])) {
            $user_name = sanitize_text_field($_POST['user_name']);
            $user_email = sanitize_email($_POST['user_email']);

            $wpdb->insert($users_table, [
                'user_name' => $user_name,
                'user_email' => $user_email
            ]);
        } elseif (isset($_POST['delete_user'])) {
            $user_id = intval($_POST['user_id']);
            $wpdb->delete($users_table, ['id' => $user_id]);
        }
    }

    $users = $wpdb->get_results("SELECT * FROM $users_table ORDER BY created_at DESC");

    echo '<div class="wrap">';
    echo '<h1>Gestión de Usuarios</h1>';

    echo '<h2>Nuevo Usuario</h2>';
    echo '<form method="POST">';
    echo '<table class="form-table"><tbody>';
    echo '<tr><th><label for="user_name">Nombre del Usuario</label></th><td><input name="user_name" type="text" id="user_name" value="" class="regular-text"></td></tr>';
    echo '<tr><th><label for="user_email">Email del Usuario</label></th><td><input name="user_email" type="email" id="user_email" value="" class="regular-text"></td></tr>';
    echo '</tbody></table>';
    echo '<p class="submit"><input type="submit" name="new_user" id="submit" class="button button-primary" value="Crear Usuario"></p>';
    echo '</form>';

    echo '<h2>Usuarios Existentes</h2>';
    if ($users) {
        echo '<table class="widefat fixed" cellspacing="0">';
        echo '<thead><tr><th>Nombre del Usuario</th><th>Email</th><th>Fecha de Creación</th><th>Acciones</th></tr></thead>';
        echo '<tbody>';
        foreach ($users as $user) {
            echo '<tr>';
            echo '<td>' . esc_html($user->user_name) . '</td>';
            echo '<td>' . esc_html($user->user_email) . '</td>';
            echo '<td>' . esc_html($user->created_at) . '</td>';
            echo '<td>';
            echo '<form method="POST" style="display:inline;"><input type="hidden" name="user_id" value="' . intval($user->id) . '"><input type="submit" name="delete_user" class="button" value="Eliminar"></form>';
            echo '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
    } else {
        echo '<p>No hay usuarios registrados.</p>';
    }

    echo '</div>';
}

// Shortcode para mostrar el diagrama de Gantt por usuario
function dtp_gantt_shortcode($atts) {
    ob_start();
    echo '<div id="gantt-chart"></div>';
    return ob_get_clean();
}
add_shortcode('dtp_gantt', 'dtp_gantt_shortcode');
