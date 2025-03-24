<?php
function mpesa_stk_push($phone, $amount) {
    $consumerKey = 'YOUR_CONSUMER_KEY';
    $consumerSecret = 'YOUR_CONSUMER_SECRET';
    $shortcode = 'YOUR_SHORTCODE';
    $passkey = 'YOUR_PASSKEY';

    $timestamp = date('YmdHis');
    $password = base64_encode($shortcode . $passkey . $timestamp);
    $callbackUrl = 'https://yourwebsite.com/mpesa-callback/';

    // Get Access Token
    $credentials = base64_encode($consumerKey . ":" . $consumerSecret);
    $response = wp_remote_post('https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials', [
        'headers' => [
            'Authorization' => 'Basic ' . $credentials,
        ],
    ]);
    $body = json_decode(wp_remote_retrieve_body($response), true);
    $accessToken = $body['access_token'];

    // STK Push Request
    $stkPushRequest = [
        'BusinessShortCode' => $shortcode,
        'Password' => $password,
        'Timestamp' => $timestamp,
        'TransactionType' => 'CustomerPayBillOnline',
        'Amount' => $amount,
        'PartyA' => $phone,
        'PartyB' => $shortcode,
        'PhoneNumber' => $phone,
        'CallBackURL' => $callbackUrl,
        'AccountReference' => 'TestPayment',
        'TransactionDesc' => 'Payment for services'
    ];

    $response = wp_remote_post('https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest', [
        'headers' => [
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json'
        ],
        'body' => json_encode($stkPushRequest)
    ]);

    return json_decode(wp_remote_retrieve_body($response), true);
}
