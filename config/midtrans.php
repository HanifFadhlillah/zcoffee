<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Midtrans Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk integrasi Midtrans Payment Gateway.
    | Isi nilai-nilai ini di file .env kamu.
    |
    | Cara mendapatkan key:
    | 1. Daftar di https://dashboard.sandbox.midtrans.com
    | 2. Login → Settings → Access Keys
    | 3. Copy Server Key dan Client Key
    |
    */

    'server_key'    => env('MIDTRANS_SERVER_KEY', ''),
    'client_key'    => env('MIDTRANS_CLIENT_KEY', ''),
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),

    /*
    | Snap URL yang digunakan di frontend (JS)
    */
    'snap_url' => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://app.midtrans.com/snap/snap.js'
        : 'https://app.sandbox.midtrans.com/snap/snap.js',
];
