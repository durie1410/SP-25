<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
        'maps_api_key' => env('GOOGLE_MAPS_API_KEY', ''),
    ],

    'vnpay' => [
        'tmn_code' => env('VNPAY_TMN_CODE', ''),
        'hash_secret' => env('VNPAY_HASH_SECRET', ''),
        'url' => env('VNPAY_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'),
        'return_url' => env('VNPAY_RETURN_URL', env('APP_URL') . '/vnpay/callback'),
        'version' => env('VNPAY_VERSION', '2.1.0'),
        'command' => env('VNPAY_COMMAND', 'pay'),
        'curr_code' => env('VNPAY_CURR_CODE', 'VND'),
        'locale' => env('VNPAY_LOCALE', 'vn'),
    ],

'gemini' => [
        'api_key' => env('GEMINI_API_KEY', ''),
    ],

 
=======
    'momo' => [
        // Ưu tiên đọc theo key mới, fallback cho các key cũ nếu có
        'endpoint'     => env('MOMO_ENDPOINT', env('MOMO_API_URL', 'https://test-payment.momo.vn/v2/gateway/api/create')),
        'partner_code' => env('MOMO_PARTNER_CODE', env('MOMO_PARTNERCODE', '')),
        'access_key'   => env('MOMO_ACCESS_KEY', env('MOMO_ACCESSKEY', '')),
        'secret_key'   => env('MOMO_SECRET_KEY', env('MOMO_SECRETKEY', '')),
        // Giữ đúng đường dẫn return/ipn cho luồng mượn & phạt hiện tại
        'return_url'   => env('MOMO_RETURN_URL', env('APP_URL') . '/borrows/momo/return'),
        'notify_url'   => env('MOMO_NOTIFY_URL', env('APP_URL') . '/borrows/momo/ipn'),
    ],
];
