<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Check if WooCommerce class exists
if (!class_exists('WC_Payment_Gateway')) {
    return;
}

class WC_Gateway_Mpesa_Bafe extends WC_Payment_Gateway
{
    public function __construct()
    {
        $this->id                 = 'mpesa_bafe';
        $this->icon               = ''; // URL to M-Pesa logo (if available)
        $this->has_fields         = true;
        $this->method_title       = 'M-Pesa Bafe';
        $this->method_description = 'Pay via M-Pesa using your mobile number.';

        // Load form fields
        $this->init_form_fields();
        $this->init_settings();

        $this->enabled          = $this->get_option('enabled');
        $this->title            = $this->get_option('title');
        $this->merchant_number  = $this->get_option('merchant_number');

        // Save admin settings
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
    }

    // Define form fields in WooCommerce settings
    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title'   => 'Enable/Disable',
                'type'    => 'checkbox',
                'label'   => 'Enable M-Pesa Payment',
                'default' => 'yes',
            ),
            'title' => array(
                'title'       => 'Title',
                'type'        => 'text',
                'description' => 'Title customers will see at checkout.',
                'default'     => 'M-Pesa Bafe Payment',
                'desc_tip'    => true,
            ),
            'merchant_number' => array(
                'title'       => 'M-Pesa Paybill / Till Number',
                'type'        => 'text',
                'description' => 'Enter your M-Pesa paybill or till number.',
                'default'     => '',
                'desc_tip'    => true,
            ),
        );
    }

    // Process payment (this just places the order on-hold)
    public function process_payment($order_id)
    {
        $order = wc_get_order($order_id);

        // Change order status to on-hold (until payment is verified)
        $order->update_status('on-hold', __('Awaiting M-Pesa payment confirmation', 'woocommerce'));

        // Reduce stock levels
        wc_reduce_stock_levels($order_id);

        // Return WooCommerce success response
        return array(
            'result'   => 'success',
            'redirect' => wc_get_checkout_url(),
        );
    }
}
