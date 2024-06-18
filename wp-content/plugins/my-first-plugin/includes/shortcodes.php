<?php

function dtp_whatsapp_button_shortcode() {
    $whatsapp_number = get_option('dtp_whatsapp_number', '');
    $whatsapp_message = get_option('dtp_whatsapp_message', 'Hola, estoy interesado en sus servicios.');
    $whatsapp_country_code = get_option('dtp_whatsapp_country_code', '34'); // Default to Spain (+34)
    $whatsapp_message_encoded = urlencode($whatsapp_message);

    if (empty($whatsapp_number)) {
        return '';
    }

    $full_number = $whatsapp_country_code . $whatsapp_number;
    $button_html = '<div class="whatsapp-button-container">
                    <a href="https://wa.me/' . esc_attr($full_number) . '?text=' . $whatsapp_message_encoded . '&type=phone_number&app_absent=0" class="whatsapp-button">Contáctanos por WhatsApp</a></div>';
    $button_html .= '<style>
        .whatsapp-button-container {
            text-align: center;
            margin-top: 20px;
        }
        .whatsapp-button {
            background-color: #25D366;
            color: white;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
        }
        .whatsapp-button:hover {
            background-color: #128C7E;
        }
    </style>';
    return $button_html;
}
add_shortcode('dtp_whatsapp_button', 'dtp_whatsapp_button_shortcode');

function dtp_gantt_shortcode($atts) {
    ob_start();
    echo '<div id="gantt-chart"></div>';
    return ob_get_clean();
}
add_shortcode('dtp_gantt', 'dtp_gantt_shortcode');
