<?php
/**
 * Plugin Name: Hair Salon Booking
 * Description: A plugin for booking hair salon appointments.
 * Version: 1.0
 * Author: Tu Nombre
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Function to create database tables
function hsb_create_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Table for bookings
    $table_name = $wpdb->prefix . 'hsb_bookings';
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name tinytext NOT NULL,
        email varchar(100) NOT NULL,
        phone varchar(20) NOT NULL,
        date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        service varchar(100) NOT NULL,
        employee varchar(100) NOT NULL,
        price float NOT NULL,
        duration int NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Table for availability
    $table_name = $wpdb->prefix . 'hsb_availability';
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        day_of_week tinyint(1) NOT NULL,
        start_time time NOT NULL,
        end_time time NOT NULL,
        is_holiday tinyint(1) NOT NULL DEFAULT 0,
        PRIMARY KEY (id)
    ) $charset_collate;";
    dbDelta($sql);

    // Table for services
    $table_name = $wpdb->prefix . 'hsb_services';
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(100) NOT NULL,
        duration int NOT NULL, -- duration in minutes
        price float NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";
    dbDelta($sql);
}

register_activation_hook(__FILE__, 'hsb_create_tables');


// Enqueue scripts and styles
function hsb_enqueue_scripts() {
    wp_enqueue_style('fullcalendar-style', 'https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css');
    wp_enqueue_style('hsb-style', plugin_dir_url(__FILE__) . 'hsb-style.css');
    wp_enqueue_script('moment-js', 'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js', array('jquery'), null, true);
    wp_enqueue_script('fullcalendar-js', 'https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js', array('jquery', 'moment-js'), null, true);
    wp_enqueue_script('hsb-script', plugin_dir_url(__FILE__) . 'hsb-script.js', array('jquery', 'fullcalendar-js'), null, true);
    wp_localize_script('hsb-script', 'hsb_ajax_obj', array('ajax_url' => admin_url('admin-ajax.php')));
}
add_action('wp_enqueue_scripts', 'hsb_enqueue_scripts');

// Handle AJAX request for booking submission
function hsb_submit_booking() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'hsb_bookings';
    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);
    $phone = sanitize_text_field($_POST['phone']);
    $date = sanitize_text_field($_POST['date']);
    $service = sanitize_text_field($_POST['service']);
    $employee = sanitize_text_field($_POST['employee']);
    $price = floatval($_POST['price']);
    $duration = intval($_POST['duration']);

    $wpdb->insert(
        $table_name,
        array(
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'date' => $date,
            'service' => $service,
            'employee' => $employee,
            'price' => $price,
            'duration' => $duration
        )
    );

    echo 'Reserva exitosa!';
    wp_die();
}
add_action('wp_ajax_hsb_submit_booking', 'hsb_submit_booking');
add_action('wp_ajax_nopriv_hsb_submit_booking', 'hsb_submit_booking');


// Handle AJAX request to get bookings
function hsb_get_bookings() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'hsb_bookings';
    $results = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
    echo json_encode($results);
    wp_die();
}
add_action('wp_ajax_hsb_get_bookings', 'hsb_get_bookings');
add_action('wp_ajax_nopriv_hsb_get_bookings', 'hsb_get_bookings');

// Handle AJAX request to get availability
function hsb_get_availability() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'hsb_availability';
    $results = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
    echo json_encode($results);
    wp_die();
}
add_action('wp_ajax_hsb_get_availability', 'hsb_get_availability');
add_action('wp_ajax_nopriv_hsb_get_availability', 'hsb_get_availability');

// Shortcode to display booking form
function hsb_booking_form_shortcode() {
    ob_start();
    ?>
    <div id="hsb-service-selection">
        <form id="hsb-service-form">
            <label for="service">Seleccione un servicio:</label>
            <select id="service" name="service" required>
                <?php
                global $wpdb;
                $services = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}hsb_services");
                foreach ($services as $service) {
                    echo '<option value="' . $service->id . '" data-duration="' . $service->duration . '" data-price="' . $service->price . '">' . $service->name . '</option>';
                }
                ?>
            </select>
            <input type="submit" value="Siguiente">
        </form>
    </div>

    <div id="hsb-booking-container" style="display: none;">
        <div id="hsb-calendar"></div>
        <form id="hsb-booking-form">
            <label for="name">Nombre:</label>
            <input type="text" id="name" name="name" required>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            <label for="phone">Teléfono:</label>
            <input type="text" id="phone" name="phone" required>
            <label for="date">Fecha y Hora:</label>
            <input type="text" id="date" name="date" required readonly>
            <input type="hidden" id="selected-service" name="service">
            <input type="hidden" id="selected-duration" name="duration">
            <input type="hidden" id="selected-price" name="price">
            <input type="submit" value="Reservar">
        </form>
        <div id="hsb-response"></div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('hsb_booking_form', 'hsb_booking_form_shortcode');


// Admin Page for Bookings
function hsb_admin_bookings_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'hsb_bookings';

    if ($_POST['action'] == 'delete_booking') {
        $id = intval($_POST['id']);
        $wpdb->delete($table_name, array('id' => $id));
    }

    $bookings = $wpdb->get_results("SELECT * FROM $table_name");
    ?>
    <div class="wrap">
        <h1>Hair Salon Bookings</h1>
        <h2>Current Bookings</h2>
        <table class="widefat fixed">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Date</th>
                    <th>Service</th>
                    <th>Employee</th>
                    <th>Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $booking) { ?>
                <tr>
                    <td><?php echo $booking->id; ?></td>
                    <td><?php echo $booking->name; ?></td>
                    <td><?php echo $booking->email; ?></td>
                    <td><?php echo $booking->phone; ?></td>
                    <td><?php echo $booking->date; ?></td>
                    <td><?php echo $booking->service; ?></td>
                    <td><?php echo $booking->employee; ?></td>
                    <td><?php echo $booking->price; ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="action" value="delete_booking">
                            <input type="hidden" name="id" value="<?php echo $booking->id; ?>">
                            <input type="submit" value="Delete" class="button button-secondary">
                        </form>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <?php
}

// Admin Menu
function hsb_admin_menu() {
    add_menu_page(
        'Hair Salon Booking',
        'Hair Salon Booking',
        'manage_options',
        'hair-salon-booking',
        'hsb_admin_page',
        'dashicons-calendar-alt'
    );

    add_submenu_page(
        'hair-salon-booking',
        'Bookings',
        'Bookings',
        'manage_options',
        'hair-salon-bookings',
        'hsb_admin_bookings_page'
    );
}
add_action('admin_menu', 'hsb_admin_menu');

// Admin Page
function hsb_admin_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'hsb_availability';

    if ($_POST['action'] == 'add_availability') {
        $day_of_week = intval($_POST['day_of_week']);
        $start_time = sanitize_text_field($_POST['start_time']);
        $end_time = sanitize_text_field($_POST['end_time']);
        $is_holiday = intval($_POST['is_holiday']);

        $wpdb->insert($table_name, array(
            'day_of_week' => $day_of_week,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'is_holiday' => $is_holiday,
        ));
    }

    if ($_POST['action'] == 'delete_availability') {
        $id = intval($_POST['id']);
        $wpdb->delete($table_name, array('id' => $id));
    }

    $availabilities = $wpdb->get_results("SELECT * FROM $table_name");
    ?>
    <div class="wrap">
        <h1>Hair Salon Booking Availability</h1>
        <form method="post">
            <input type="hidden" name="action" value="add_availability">
            <table>
                <tr>
                    <th>Day of Week</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Is Holiday</th>
                </tr>
                <tr>
                    <td>
                        <select name="day_of_week">
                            <option value="0">Sunday</option>
                            <option value="1">Monday</option>
                            <option value="2">Tuesday</option>
                            <option value="3">Wednesday</option>
                            <option value="4">Thursday</option>
                            <option value="5">Friday</option>
                            <option value="6">Saturday</option>
                        </select>
                    </td>
                    <td><input type="time" name="start_time"></td>
                    <td><input type="time" name="end_time"></td>
                    <td><input type="checkbox" name="is_holiday" value="1"></td>
                </tr>
            </table>
            <input type="submit" value="Add Availability" class="button button-primary">
        </form>
        <h2>Current Availabilities</h2>
        <table class="widefat fixed">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Day of Week</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Is Holiday</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($availabilities as $availability) { ?>
                <tr>
                    <td><?php echo $availability->id; ?></td>
                    <td><?php echo $availability->day_of_week; ?></td>
                    <td><?php echo $availability->start_time; ?></td>
                    <td><?php echo $availability->end_time; ?></td>
                    <td><?php echo $availability->is_holiday ? 'Yes' : 'No'; ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="action" value="delete_availability">
                            <input type="hidden" name="id" value="<?php echo $availability->id; ?>">
                            <input type="submit" value="Delete" class="button button-secondary">
                        </form>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <?php
}

// Filter events based on availability
function hsb_filter_events($events) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'hsb_availability';

    $availabilities = $wpdb->get_results("SELECT * FROM $table_name WHERE is_holiday = 0");
    $holidays = $wpdb->get_results("SELECT * FROM $table_name WHERE is_holiday = 1");

    $filtered_events = array();

    foreach ($events as $event) {
        $event_start = new DateTime($event['start']);
        $event_end = new DateTime($event['end']);
        $day_of_week = $event_start->format('w');

        $is_available = false;
        foreach ($availabilities as $availability) {
            if ($availability->day_of_week == $day_of_week) {
                $availability_start = new DateTime($event_start->format('Y-m-d') . ' ' . $availability->start_time);
                $availability_end = new DateTime($event_start->format('Y-m-d') . ' ' . $availability->end_time);

                if ($event_start >= $availability_start && $event_end <= $availability_end) {
                    $is_available = true;
                    break;
                }
            }
        }

        foreach ($holidays as $holiday) {
            $holiday_date = new DateTime($event_start->format('Y-m-d') . ' ' . $holiday->start_time);
            if ($event_start->format('Y-m-d') == $holiday_date->format('Y-m-d')) {
                $is_available = false;
                break;
            }
        }

        if ($is_available) {
            $filtered_events[] = $event;
        }
    }

    return $filtered_events;
}
add_filter('fullcalendar_events', 'hsb_filter_events');
?>
