# ApiForge

File-first API schema engine for Laravel. Define APIs in PHP, compile a runtime registry, generate OpenAPI + HTML docs, and protect endpoints with stateless signed API keys.

## Requirements

- PHP 8.3+
- Laravel 11 or 12

## Installation

```bash
composer require apiforge/apiforge
php artisan apiforge:install
```

## Quick Start

1. Add a schema file in `api-forge/schemas/`:

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
php artisan apiforge:scan
```

3. Generate documentation:

```bash
php artisan apiforge:docs
```

4. Open `api-forge/docs/index.html` in your browser.

## Middleware

Register the API key middleware on your API routes:

```php
Route::middleware(['api', 'apiforge.key'])->group(base_path('routes/api.php'));
```

Protected schemas (`auth => true`) require a valid `X-Api-Key` header.

## API Keys

Create signed keys programmatically:

```php
use ApiForge\Contracts\ApiKeyManagerContract;

$key = app(ApiKeyManagerContract::class)->create(
    name: 'Partner Integration',
    mode: 'live',
    scopes: ['jobs:create'],
    permissions: ['POST:/jobs'],
);
```

Keys are verified without a database lookup. Revocation is cache-backed.

## Configuration

Publish and edit `config/apiforge.php` to customize schema paths, cache store, documentation output, and signing key (`APIFORGE_SIGNING_KEY`).

## Commands

| Command | Description |
|---------|-------------|
| `apiforge:install` | Publish config and scaffold directories |
| `apiforge:scan` | Load and cache schemas into the registry |
| `apiforge:docs` | Generate `openapi.json` and `index.html` |

## License

MIT
