<?php

// Settings to create the admin page 

add_action('admin_menu', 'reminder_to_cancelled_admin_page', 999);

function reminder_to_cancelled_admin_page()
{
    add_submenu_page(
        'woocommerce',
        'Recordatorio a cancelado',
        'Recordatorio a cancelado',
        'edit_products',
        'reminder_to_cancelled',
        'reminder_to_cancelled_timer_page_callback',
        999
    );

    add_action('admin_init', 'rtoc_settings_group');
}

function reminder_to_cancelled_timer_page_callback()
{
    require_once __DIR__ . '/templates/settings-page.php';
}

function rtoc_settings_group()
{

    register_setting(
        'rtoc_settings_group',   // Settings group name
        'time_to_cancel',    // Option name
        'sanitize_text_field'  // Arguments or sanitize funcion
    );

    add_settings_section(
        'rtoc-cancel-options',
        'Tiempo para cancelar recordatorios',
        'rtoc_cancel_options',
        'rtoc-settings'
    );

    add_settings_field(
        'rtoc-time-option',
        'Cantidad de días para cancelar pedidos en recordatorio',
        'rtoc_time',
        'rtoc-settings',
        'rtoc-cancel-options'
    );
}

function rtoc_cancel_options()
{
    echo '<hr><h3>Escriba la cantidad de días que deben transcurrir para que un pedido en estado de recordatorio pase a cancelado </h3>';
}

function rtoc_time()
{
    $actual = trim(esc_attr(get_option('time_to_cancel'))) ? trim(esc_attr(get_option('time_to_cancel'))) : 1;
    echo "<input type='number' name='time_to_cancel' value='$actual'/>";
    echo '<br><em>Sólo se pueden escribir números</em><hr>';


    echo '<h4>Configuración actual: ' . $actual . ' días</h4>';
}
