<?php
/*
Plugin Name: My Appointments Plugin
Description: Un plugin para reservar citas diarias.
Version: 1.0
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
