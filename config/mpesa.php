<?php

$environment = strtolower((string) env('MPESA_ENVIRONMENT', 'sandbox'));
$isSandbox = $environment !== 'production';

return [

    /*
    |--------------------------------------------------------------------------
    | Daraja Environment
    |--------------------------------------------------------------------------
    |
    | Use "sandbox" for testing. Switch to "production" only after Sandbox
    | STK Push → callback → POS paid flow is verified end-to-end.
    |
    */

    'environment' => $environment,

    'consumer_key' => env('MPESA_CONSUMER_KEY'),
    'consumer_secret' => env('MPESA_CONSUMER_SECRET'),
    'shortcode' => env('MPESA_SHORTCODE'),
    'passkey' => env('MPESA_PASSKEY'),

    /*
    |--------------------------------------------------------------------------
    | Callback URL
    |--------------------------------------------------------------------------
    |
    | Must be a publicly reachable HTTPS URL. Daraja cannot call localhost.
    | For local development use a tunnel (e.g. ngrok) or a staging host.
    |
    */

    'callback_url' => env('MPESA_CALLBACK_URL'),

    /*
    |--------------------------------------------------------------------------
    | Transaction Type
    |--------------------------------------------------------------------------
    |
    | CustomerPayBillOnline — Paybill shortcode
    | CustomerBuyGoodsOnline — Till / Buy Goods (production merchant setup)
    |
    */

    'transaction_type' => env('MPESA_TRANSACTION_TYPE', 'CustomerPayBillOnline'),

    'oauth_url' => $isSandbox
        ? 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials'
        : 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials',

    'stk_push_url' => $isSandbox
        ? 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest'
        : 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest',

    'timeout' => (int) env('MPESA_HTTP_TIMEOUT', 30),

    'token_cache_key' => 'mpesa.daraja.access_token',

    'token_cache_buffer_seconds' => 60,

];
