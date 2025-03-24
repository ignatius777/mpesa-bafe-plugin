<?php
/**
 * Plugin Name: M-Pesa Bafe Plugin
 * Plugin URI:  https://yourwebsite.com
 * Description: Custom WooCommerce M-Pesa Payment Gateway using STK Push.
 * Version: 1.0
 * Author: Ignatius Eugene
 * Author URI: https://yourwebsite.com
 * License: GPL2
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Check if WooCommerce is installed
if (!class_exists('WC_Payment_Gateway')) {
    add_action('admin_notices', function () {
        echo '<div class="error"><p><strong>M-Pesa Bafe Plugin</strong> requires WooCommerce to be installed and active.</p></div>';
    });
    return;
}

// Include the payment gateway class
require_once plugin_dir_path(__FILE__) . 'class-mpesa-gateway.php';

// Register the payment gateway with WooCommerce
function mpesa_bafe_add_gateway($gateways)
{
    $gateways[] = 'WC_Gateway_Mpesa_Bafe';
    return $gateways;
}
add_filter('woocommerce_payment_gateways', 'mpesa_bafe_add_gateway');

// Add the callback handler for payment confirmation
require_once plugin_dir_path(__FILE__) . 'mpesa_callback.php';

// Add an admin notice if API credentials are missing
function mpesa_bafe_admin_notice()
{
    $options = get_option('woocommerce_mpesa_bafe_settings');
    if (empty($options['consumer_key']) || empty($options['consumer_secret'])) {
        echo '<div class="error"><p><strong>M-Pesa Bafe Plugin:</strong> Please enter your M-Pesa API credentials in WooCommerce settings.</p></div>';
    }
}
add_action('admin_notices', 'mpesa_bafe_admin_notice');
