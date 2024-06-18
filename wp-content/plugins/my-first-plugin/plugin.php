<?php
/*
Plugin Name: Plugin
Description: Un plugin para gestionar tareas diarias con imágenes, usuarios, diagrama de Gantt y un botón de WhatsApp.
Version: 1.4
Author: Ramiro
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

require_once plugin_dir_path(__FILE__) . 'includes/activate.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin-menu.php';
require_once plugin_dir_path(__FILE__) . 'includes/scripts.php';
require_once plugin_dir_path(__FILE__) . 'includes/ajax-handlers.php';
require_once plugin_dir_path(__FILE__) . 'includes/shortcodes.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin-pages.php';
