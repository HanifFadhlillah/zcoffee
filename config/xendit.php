<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Xendit Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk integrasi Xendit Payment Gateway (QRIS Dinamis).
    |
    */

    'secret_key'    => env('XENDIT_SECRET_KEY', ''),
    'webhook_token' => env('XENDIT_WEBHOOK_TOKEN', ''),
    'is_production' => env('XENDIT_IS_PRODUCTION', false),
];
