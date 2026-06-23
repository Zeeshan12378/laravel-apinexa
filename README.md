# APINEXA

File-first API schema engine for Laravel. Define APIs in PHP, compile a runtime registry, generate OpenAPI + HTML docs, and protect endpoints with stateless signed API keys.

## Requirements

- PHP 8.3+
- Laravel 11 or 12

## Installation

```bash
composer require zeeshanmushtq/apinexa
php artisan apinexa:install
```

## Quick Start

1. Add a schema file in `api-nexa/schemas/`:

```php
<?php

return [
    'name' => 'Create Job',
    'method' => 'POST',
    'endpoint' => '/jobs',
    'auth' => true,
    'roles' => ['admin', 'employer'],
    'scopes' => ['jobs:create'],
    'payload' => [
        'title' => 'required|string',
        'salary' => 'nullable|numeric',
    ],
];
```

2. Compile schemas:

```bash
php artisan apinexa:scan
```

3. Generate documentation:

```bash
php artisan apinexa:docs
```

4. Open `api-nexa/docs/index.html` in your browser.

## Middleware

Register the API key middleware on your API routes:

```php
Route::middleware(['api', 'apinexa.key'])->group(base_path('routes/api.php'));
```

Protected schemas (`auth => true`) require a valid `X-Api-Key` header.

## API Keys

Create signed keys programmatically:

```php
use ZeeshanMushtaq\ApiNexa\Contracts\ApiKeyManagerContract;

$key = app(ApiKeyManagerContract::class)->create(
    name: 'Partner Integration',
    mode: 'live',
    scopes: ['jobs:create'],
    permissions: ['POST:/jobs'],
);
```

Keys are verified without a database lookup. Revocation is cache-backed.

## Configuration

Publish and edit `config/apinexa.php` to customize schema paths, cache store, documentation output, and signing key (`APINEXA_SIGNING_KEY`).

## Commands

| Command | Description |
|---------|-------------|
| `APINEXA:install` | Publish config and scaffold directories |
| `APINEXA:scan` | Load and cache schemas into the registry |
| `APINEXA:docs` | Generate `openapi.json` and `index.html` |

## License

MIT

