<?php

namespace ZeeshanMushtaq\ApiNexa\Contracts;

use ZeeshanMushtaq\ApiNexa\Support\SignedApiKey;

interface ApiKeyManagerContract
{
    /**
     * @param  array<int, string>  $scopes
     * @param  array<int, string>  $permissions
     */
    public function create(
        string $name,
        string $mode = 'live',
        array $scopes = [],
        array $permissions = [],
        ?int $ttlDays = null,
    ): SignedApiKey;

    public function revoke(string $keyId): void;

    public function isRevoked(string $keyId): bool;
}

