<?php

return [

    'name' => env('APP_NAME', 'POS System'),

    'env' => env('APP_ENV', 'production'),

    'debug' => (bool) env('APP_DEBUG', false),

    'url' => env('APP_URL', 'http://localhost'),

    'timezone' => env('APP_TIMEZONE', 'Asia/Bangkok'),

    'locale' => env('APP_LOCALE', 'th'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'th_TH'),

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store'  => env('APP_MAINTENANCE_STORE', 'database'),
    ],

    /*
    |--------------------------------------------------------------------------
    | ข้อมูลร้านค้า (ใช้แสดงบนหัวใบเสร็จและรายงาน)
    |--------------------------------------------------------------------------
    */
    'shop' => [
        'name'     => env('SHOP_NAME', 'ร้านสะดวกซื้อ KAN MART'),
        'address'  => env('SHOP_ADDRESS', '-'),
        'phone'    => env('SHOP_PHONE', '-'),
        'tax_id'   => env('SHOP_TAX_ID', '-'),
        'vat_rate' => (float) env('SHOP_VAT_RATE', 7),
    ],

];
