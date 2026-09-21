<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Space-Market API
    |--------------------------------------------------------------------------
    |
    | Values served by GET /v3/info/ so clients know how to render amounts and
    | which defaults to pre-fill when creating products.
    | See https://space-market.github.io/API/swagger.json
    |
    */

    'version' => '3.0.0',

    'currency' => env('SPACEMARKET_CURRENCY', '€'),

    'currency_before' => env('SPACEMARKET_CURRENCY_BEFORE', false),

    /**
     * Set to null for currencies without a subdivision.
     */
    'decimal_separator' => env('SPACEMARKET_DECIMAL_SEPARATOR', ','),

    'energy' => env('SPACEMARKET_ENERGY_UNIT', 'kcal'),

    /**
     * Credit limit in cents, or null to disable it.
     */
    'global_credit_limit' => env('SPACEMARKET_CREDIT_LIMIT'),

    'defaults' => [
        'price' => (int) env('SPACEMARKET_DEFAULT_PRICE', 150),
        'package_size' => env('SPACEMARKET_DEFAULT_PACKAGE_SIZE', '0,5 l'),
        'caffeine' => null,
        'alcohol' => null,
        'energy' => null,
        'sugar' => null,
        'active' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Image uploads
    |--------------------------------------------------------------------------
    */

    'max_image_kilobytes' => (int) env('SPACEMARKET_MAX_IMAGE_KILOBYTES', 4096),

];
