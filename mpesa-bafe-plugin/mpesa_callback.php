<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Check if M-Pesa callback data is received
if (isset($_GET['mpesa_callback'])) {
    $mpesa_response = file_get_contents("php://input");
    $response = json_decode($mpesa_response, true);

    if (!isset($response['Body']['stkCallback'])) {
        die("Invalid M-Pesa response");
    }

    $callback = $response['Body']['stkCallback'];
    $result_code = $callback['ResultCode'];
    $order_id = $callback['CallbackMetadata']['Item'][1]['Value']; // Get order ID
    $amount_paid = $callback['CallbackMetadata']['Item'][0]['Value']; // Get paid amount

    $order = wc_get_order($order_id);
    if (!$order) {
        die("Order not found");
    }

    // Check if payment was successful
    if ($result_code == "0") {
        $order_total = $order->get_total();

        // Validate the exact amount is paid
        if ($amount_paid == $order_total) {
            $order->payment_complete();
            $order->add_order_note("M-Pesa Payment Received: KES {$amount_paid}");
            
            // Generate invoice
            $order->update_status('completed');
            $order->save();
        } else {
            $order->add_order_note("M-Pesa Payment Mismatch! Expected KES {$order_total}, but received KES {$amount_paid}.");
            $order->update_status('on-hold');
        }
    } else {
        $order->add_order_note("M-Pesa Payment Failed. Result Code: $result_code");
        $order->update_status('failed');
    }

    exit;
}
?>
