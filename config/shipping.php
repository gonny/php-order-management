<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Carrier
    |--------------------------------------------------------------------------
    |
    | The default shipping carrier to use when none is specified.
    |
    */

    'default' => env('SHIPPING_DEFAULT_CARRIER', 'dpd'),

    /*
    |--------------------------------------------------------------------------
    | Carrier Configurations
    |--------------------------------------------------------------------------
    |
    | Configuration for different shipping carriers.
    |
    */

    'carriers' => [
        'balikovna' => [
            'name' => 'Balíkovna',
            'api_url' => env('BALIKOVNA_API_URL', 'https://api.balikovna.cz/v1'),
            'api_key' => env('BALIKOVNA_API_KEY'),
            'webhook_secret' => env('BALIKOVNA_WEBHOOK_SECRET'),
            'enabled' => env('BALIKOVNA_ENABLED', true),
            'test_mode' => env('BALIKOVNA_TEST_MODE', true),
            'default_service' => 'standard',
            'max_weight' => 20000, // 20kg in grams
            'max_dimensions' => [
                'length' => 100, // cm
                'width' => 50,   // cm
                'height' => 50,  // cm
            ],
        ],
        'dpd' => [
            'name' => 'DPD',
            'api_url' => env('DPD_API_URL', 'https://geoapi.dpd.cz/v1'),
            'api_key' => env('DPD_API_KEY'),
            'username' => env('DPD_USERNAME'),
            'password' => env('DPD_PASSWORD'),
            'webhook_secret' => env('DPD_WEBHOOK_SECRET'),
            'enabled' => env('DPD_ENABLED', true),
            'test_mode' => env('DPD_TEST_MODE', true),
            'default_service' => 'standard',
            'max_weight' => 31500, // 31.5kg in grams
            'max_dimensions' => [
                'length' => 175, // cm
                'width' => 75,   // cm
                'height' => 70,  // cm
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Label Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for shipping label generation and storage.
    |
    */

    'labels' => [
        'storage_disk' => env('SHIPPING_LABELS_DISK', 'local'),
        'storage_path' => env('SHIPPING_LABELS_PATH', 'shipping-labels'),
        'default_format' => env('SHIPPING_LABELS_FORMAT', 'pdf'),
        'default_size' => env('SHIPPING_LABELS_SIZE', 'A4'),
        'cleanup_after_days' => env('SHIPPING_LABELS_CLEANUP_DAYS', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for incoming webhooks from shipping carriers.
    |
    */

    'webhooks' => [
        'verify_signatures' => env('SHIPPING_VERIFY_WEBHOOK_SIGNATURES', true),
        'timeout' => env('SHIPPING_WEBHOOK_TIMEOUT', 10), // seconds
        'retry_attempts' => env('SHIPPING_WEBHOOK_RETRY_ATTEMPTS', 3),
    ],

];
