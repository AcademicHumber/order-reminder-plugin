<?php

/**
 * The plugin bootstrap file
 *
 *
 * @link              https://www.linkedin.com/in/adrian-fernandez-1701/
 * @since             1.0.0
 * @package           Order_Reminder
 *
 * @wordpress-plugin
 * Plugin Name:       Order Reminder
 * Plugin URI:        https://github.com/AcademicHumber/order-reminder-plugin
 * Description:       Plugin para monitorizar el tiempo en días que un producto pasa de "recordatorio" a "cancelado" automáticamente.
 * Version:           1.0.0
 * Author:            Adrian Fernandez
 * Author URI:        https://www.linkedin.com/in/adrian-fernandez-1701/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       order-reminder
 */

require_once __DIR__ . '/includes/settings.php';

function custom_admin_notice($order_id)
{
?>
    <div class="notice notice-success is-dismissible">
        <p>Se ha cancelado el pedido #
            <?php echo $order_id ?> debido a que el tiempo para el depósito ha vencido
        </p>
    </div>
<?php
}

function cancel_reminded_orders()
{
    global $wpdb;

    $results = $wpdb->get_results("SELECT order_id FROM `{$wpdb->prefix}wc_order_stats` WHERE status='wc-reminder'");

    foreach ($results as $result) {
        $order_id = (int)$result->order_id;
        try {
            $days_to_cancel = $actual = trim(esc_attr(get_option('time_to_cancel'))) ? trim(esc_attr(get_option('time_to_cancel'))) : 1;
            $time_to_cancel = date("Y-m-d H:i:s", strtotime("-$days_to_cancel day -4 hours"));
            $order = new WC_Order($order_id);
            $last_modified = $order->modified_date;
            if ($last_modified < $time_to_cancel) {
                $order->update_status('wc-cancelled', 'Tiempo de espera para tranferencia luego del recordatorio terminado.', true);
                custom_admin_notice($order_id);
            }
        } catch (\Throwable $th) {
        }
    }
}

add_action('admin_init', 'cancel_reminded_orders');

// Send email to customer when order has been cancelled or failed 

add_action('woocommerce_order_status_changed', 'send_custom_email_notifications', 10, 4);
function send_custom_email_notifications($order_id, $old_status, $new_status, $order)
{
    if ($new_status == 'cancelled' || $new_status == 'failed') {
        $wc_emails = WC()->mailer()->get_emails(); // Get all WC_emails objects instances
        $customer_email = $order->get_billing_email(); // The customer email
    }

    if ($new_status == 'cancelled') {
        // change the recipient of this instance
        $wc_emails['WC_Email_Cancelled_Order']->recipient = $customer_email;
        // Sending the email from this instance
        $wc_emails['WC_Email_Cancelled_Order']->trigger($order_id);
    } elseif ($new_status == 'failed') {
        // Add a recipient in this instance
        $wc_emails['WC_Email_Failed_Order']->recipient .= ',' . $customer_email;
        // Sending the email from this instance
        $wc_emails['WC_Email_Failed_Order']->trigger($order_id);
    }
}
