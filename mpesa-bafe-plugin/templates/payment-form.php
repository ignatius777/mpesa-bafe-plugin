<?php
if (class_exists('WooCommerce')) {
    $cart_total = WC()->cart->total; // Get WooCommerce cart total
} else {
    $cart_total = 0;
}
?>

<form method="post">
    <input type="text" name="phone" placeholder="Enter M-Pesa number" required>
    <input type="hidden" name="amount" value="<?php echo esc_attr($cart_total); ?>"> <!-- Hidden cart total -->
    <p>Total: <strong>KES <?php echo esc_html($cart_total); ?></strong></p>
    <button type="submit" name="mpesa_pay">Pay with M-Pesa</button>
</form>

<?php
if (isset($_POST['mpesa_pay'])) {
    $phone = sanitize_text_field($_POST['phone']);
    $amount = sanitize_text_field($_POST['amount']);
    $response = mpesa_stk_push($phone, $amount);
    echo '<pre>';
    print_r($response);
    echo '</pre>';
}
?>
