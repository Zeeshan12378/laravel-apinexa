<?php

return [

    /*
    |--------------------------------------------------------------------------
    | ApiForge Enabled
    |--------------------------------------------------------------------------
    */

    'enabled' => env('APIFORGE_ENABLED', true),

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
            base_path('api-forge/schemas'),
        ],
        'pattern' => '*.php',
        'hot_reload' => env('APIFORGE_HOT_RELOAD', env('APP_ENV') === 'local'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default API Version
    |--------------------------------------------------------------------------
    */

    'default_version' => env('APIFORGE_DEFAULT_VERSION', 'v1'),

    /*
    |--------------------------------------------------------------------------
    | Registry Cache
    |--------------------------------------------------------------------------
    */

    'cache' => [
        'enabled' => env('APIFORGE_CACHE_ENABLED', true),
        'store' => env('APIFORGE_CACHE_STORE'),
        'prefix' => env('APIFORGE_CACHE_PREFIX', 'apiforge'),
        'ttl' => env('APIFORGE_CACHE_TTL', 86400),
        'registry_key' => 'registry',
    ],

    /*
    |--------------------------------------------------------------------------
    | Documentation Output
    |--------------------------------------------------------------------------
    */

    'documentation' => [
        'output_path' => base_path('api-forge/docs'),
        'openapi_filename' => 'openapi.json',
        'html_filename' => 'index.html',
        'title' => env('APIFORGE_DOCS_TITLE', 'API Documentation'),
        'version' => env('APIFORGE_DOCS_VERSION', '1.0.0'),
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
        'signing_key' => env('APIFORGE_SIGNING_KEY'),
        'default_ttl_days' => (int) env('APIFORGE_KEY_TTL_DAYS', 90),
        'header' => env('APIFORGE_KEY_HEADER', 'X-Api-Key'),
        'revocation_prefix' => 'keys.revoked',
    ],

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    */

    'middleware' => [
        'alias' => 'apiforge.key',
    ],

];
