<?php

namespace ZeeshanMushtaq\ApiNexa\Tests\Unit;

use ZeeshanMushtaq\ApiNexa\Auth\ApiKeyManager;
use ZeeshanMushtaq\ApiNexa\Auth\SignatureValidator;
use ZeeshanMushtaq\ApiNexa\Exceptions\InvalidApiKeyException;
use ZeeshanMushtaq\ApiNexa\Tests\TestCase;

class ApiKeyTest extends TestCase
{
    public function test_it_creates_and_verifies_signed_keys(): void
    {
        $manager = $this->app->make(ApiKeyManager::class);
        $validator = $this->app->make(SignatureValidator::class);

        $signed = $manager->create(
            name: 'Test Key',
            mode: 'test',
            scopes: ['jobs:create'],
            permissions: ['POST:/jobs'],
            ttlDays: 30,
        );

        $verified = $validator->verify($signed->key);

        $this->assertSame($signed->keyId, $verified->keyId);
        $this->assertTrue($verified->hasScope('jobs:create'));
        $this->assertTrue($verified->hasPermission('POST:/jobs'));
    }

    public function test_revoked_keys_are_rejected(): void
    {
        $manager = $this->app->make(ApiKeyManager::class);
        $validator = $this->app->make(SignatureValidator::class);

        $signed = $manager->create('Revocable');
        $manager->revoke($signed->keyId);

        $this->expectException(InvalidApiKeyException::class);
        $validator->verify($signed->key);
    }
}

