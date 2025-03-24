<?php
/**
 * Plugin Name: M-Pesa Bafe Payment Gateway
 * Plugin URI:  https://example.com
 * Description: M-Pesa STK Push Payment Gateway for WooCommerce with Payment Validation.
 * Version:     1.0.0
 * Author:      Ignatius Eugene
 * Author URI:  https://example.com
 * License:     GPL2
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('plugins_loaded', 'mpesa_init_gateway');

function mpesa_init_gateway() {
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    class WC_Mpesa_Gateway extends WC_Payment_Gateway {
        public function __construct() {
            $this->id = 'mpesa';
            $this->method_title = 'M-Pesa Payment';
            $this->method_description = 'Pay using M-Pesa STK Push.';
            $this->supports = array('products');

            $this->init_form_fields();
            $this->init_settings();
            
            $this->enabled = $this->get_option('enabled');
            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');
            $this->business_shortcode = $this->get_option('business_shortcode');
            $this->consumer_key = $this->get_option('consumer_key');
            $this->consumer_secret = $this->get_option('consumer_secret');
            $this->passkey = $this->get_option('passkey');

            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        }

        public function init_form_fields() {
            $this->form_fields = array(
                'enabled' => array(
                    'title' => 'Enable/Disable',
                    'type' => 'checkbox',
                    'label' => 'Enable M-Pesa Payment',
                    'default' => 'yes'
                ),
                'title' => array(
                    'title' => 'Title',
                    'type' => 'text',
                    'description' => 'This controls the title for the payment method during checkout.',
                    'default' => 'M-Pesa Payment',
                    'desc_tip' => true
                ),
                'description' => array(
                    'title' => 'Description',
                    'type' => 'textarea',
                    'description' => 'Payment method description at checkout.',
                    'default' => 'Pay using M-Pesa STK Push.'
                ),
                'business_shortcode' => array(
                    'title' => 'M-Pesa Business Shortcode',
                    'type' => 'text',
                    'description' => 'Enter your M-Pesa Paybill or Till Number.',
                    'default' => ''
                ),
                'consumer_key' => array(
                    'title' => 'Consumer Key',
                    'type' => 'text',
                    'description' => 'Enter your M-Pesa API Consumer Key.',
                    'default' => ''
                ),
                'consumer_secret' => array(
                    'title' => 'Consumer Secret',
                    'type' => 'text',
                    'description' => 'Enter your M-Pesa API Consumer Secret.',
                    'default' => ''
                ),
                'passkey' => array(
                    'title' => 'Passkey',
                    'type' => 'text',
                    'description' => 'Enter your M-Pesa API Passkey.',
                    'default' => ''
                ),
            );
        }

        public function process_payment($order_id) {
            $order = wc_get_order($order_id);
            $phone = sanitize_text_field($_POST['billing_phone']);
            $amount = $order->get_total();

            $response = mpesa_stk_push($phone, $amount, $order_id);

            if (isset($response['ResponseCode']) && $response['ResponseCode'] == '0') {
                $order->add_order_note("M-Pesa STK Push Sent. Waiting for customer to confirm.");
                return array(
                    'result' => 'success',
                    'redirect' => $this->get_return_url($order)
                );
            } else {
                wc_add_notice('M-Pesa payment failed. Please try again.', 'error');
                return;
            }
        }
    }

    add_filter('woocommerce_payment_gateways', function($methods) {
        $methods[] = 'WC_Mpesa_Gateway';
        return $methods;
    });
}

// Load Callback Handler
require_once plugin_dir_path(__FILE__) . 'mpesa_callback.php';

?>
