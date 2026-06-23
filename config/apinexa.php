<?php

return [

    /*
    |--------------------------------------------------------------------------
    | APINEXA Enabled
    |--------------------------------------------------------------------------
    */

    'enabled' => env('APINEXA_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Schema Paths
    |--------------------------------------------------------------------------
    |
    | Directories containing PHP schema definition files.
    |
    */

    'schemas' => [
        'paths' => [
            base_path('api-nexa/schemas'),
        ],
        'pattern' => '*.php',
        'hot_reload' => env('APINEXA_HOT_RELOAD', env('APP_ENV') === 'local'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default API Version
    |--------------------------------------------------------------------------
    */

    'default_version' => env('APINEXA_DEFAULT_VERSION', 'v1'),

    /*
    |--------------------------------------------------------------------------
    | Registry Cache
    |--------------------------------------------------------------------------
    */

    'cache' => [
        'enabled' => env('APINEXA_CACHE_ENABLED', true),
        'store' => env('APINEXA_CACHE_STORE'),
        'prefix' => env('APINEXA_CACHE_PREFIX', 'APINEXA'),
        'ttl' => env('APINEXA_CACHE_TTL', 86400),
        'registry_key' => 'registry',
    ],

    /*
    |--------------------------------------------------------------------------
    | Documentation Output
    |--------------------------------------------------------------------------
    */

    'documentation' => [
        'output_path' => base_path('api-nexa/docs'),
        'openapi_filename' => 'openapi.json',
        'html_filename' => 'index.html',
        'title' => env('APINEXA_DOCS_TITLE', 'API Documentation'),
        'version' => env('APINEXA_DOCS_VERSION', '1.0.0'),
    ],

    /*
    |--------------------------------------------------------------------------
    | API Keys
    |--------------------------------------------------------------------------
    |
    | Stateless signed API keys. No database lookup per request.
    |
    */

    'keys' => [
        'signing_key' => env('APINEXA_SIGNING_KEY'),
        'default_ttl_days' => (int) env('APINEXA_KEY_TTL_DAYS', 90),
        'header' => env('APINEXA_KEY_HEADER', 'X-Api-Key'),
        'revocation_prefix' => 'keys.revoked',
    ],

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    */

    'middleware' => [
        'alias' => 'apinexa.key',
    ],

];

