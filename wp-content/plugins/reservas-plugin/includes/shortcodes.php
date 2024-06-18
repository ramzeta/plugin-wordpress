<?php

function map_appointment_form_shortcode() {
    if (!is_user_logged_in()) {
        echo '<div class="login-message">Por favor, <a href="' . wp_login_url(get_permalink()) . '">inicia sesión con Google</a> para reservar una cita.</div>';
        return;
    }

    ob_start();
    ?>
    <div id="booking-container">
        <div id="calendar-container">
            <div id="calendar-header">
                <button id="prev-month">«</button>
                <h2 id="calendar-title"></h2>
                <button id="next-month">»</button>
            </div>
            <div id="calendar-days"></div>
        </div>
        <form id="appointment-form">
            <div id="selected-date"></div>
            <label for="service">Servicio</label>
            <select id="service" name="service_id">
                <option value="1">Corte Degradado desde 0 (Tendencia)</option>
            </select>
            <label for="appointment_time">Hora</label>
            <select id="appointment_time" name="appointment_time">
                <!-- Opciones de tiempo cargadas dinámicamente -->
            </select>
            <div id="appointment-summary">
                <p>Total: <span id="total-amount">15,00 €</span></p>
                <p>Duración: <span id="appointment-duration">30 min</span></p>
            </div>
            <button type="submit">Continuar</button>
        </form>
        <div id="form-message"></div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('map_appointment_form', 'map_appointment_form_shortcode');
