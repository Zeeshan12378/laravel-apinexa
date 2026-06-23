<?php

namespace ZMJCoder\ApiNexa\Tests\Feature;

use ZMJCoder\ApiNexa\Auth\ApiKeyManager;
use ZMJCoder\ApiNexa\Contracts\ApiRegistryContract;
use ZMJCoder\ApiNexa\Middleware\ApiKeyMiddleware;
use ZMJCoder\ApiNexa\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class ApiKeyMiddlewareTest extends TestCase
{
    public function test_middleware_allows_public_endpoints(): void
    {
        Route::middleware(ApiKeyMiddleware::class)->get('/jobs', fn () => response()->json(['ok' => true]));

        $this->app->make(ApiRegistryContract::class)->reload();

        $this->get('/jobs')->assertOk()->assertJson(['ok' => true]);
    }

    public function test_middleware_requires_key_for_protected_endpoints(): void
    {
        Route::middleware(ApiKeyMiddleware::class)->post('/jobs', fn () => response()->json(['created' => true]));

        $this->app->make(ApiRegistryContract::class)->reload();

        $this->post('/jobs')->assertUnauthorized();
    }

    public function test_middleware_accepts_valid_key(): void
    {
        Route::middleware(ApiKeyMiddleware::class)->post('/jobs', function (Request $request) {
            return response()->json([
                'created' => true,
                'key' => $request->attributes->get('APINEXA.api_key')?->keyId,
            ]);
        });

        $this->app->make(ApiRegistryContract::class)->reload();

        $key = $this->app->make(ApiKeyManager::class)->create(
            name: 'Middleware Test',
            scopes: ['jobs:create'],
            permissions: ['POST:/jobs'],
        );

        $this->post('/jobs', [], ['X-Api-Key' => $key->key])
            ->assertOk()
            ->assertJson(['created' => true, 'key' => $key->keyId]);
    }
}

