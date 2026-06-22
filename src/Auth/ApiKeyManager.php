<?php

namespace ApiForge\Auth;

use ApiForge\Contracts\ApiKeyManagerContract;
use ApiForge\Contracts\SignatureValidatorContract;
use ApiForge\Support\SignedApiKey;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Str;

class ApiKeyManager implements ApiKeyManagerContract
{
    public function __construct(
        protected SignatureValidatorContract $validator,
        protected CacheRepository $cache,
    ) {}

    public function create(
        string $name,
        string $mode = 'live',
        array $scopes = [],
        array $permissions = [],
        ?int $ttlDays = null,
    ): SignedApiKey {
        $mode = in_array($mode, ['live', 'test'], true) ? $mode : 'live';
        $ttlDays ??= (int) config('apiforge.keys.default_ttl_days', 90);
        $issuedAt = time();
        $expiresAt = $issuedAt + ($ttlDays * 86400);
        $keyId = 'key_'.Str::lower(Str::random(16));

        $claims = [
            'kid' => $keyId,
            'name' => $name,
            'mode' => $mode,
            'scopes' => array_values($scopes),
            'permissions' => array_values($permissions),
            'iat' => $issuedAt,
            'exp' => $expiresAt,
        ];

        $key = $this->validator->sign($claims);

        return new SignedApiKey(
            key: $key,
            keyId: $keyId,
            mode: $mode,
            scopes: array_values($scopes),
            permissions: array_values($permissions),
            issuedAt: $issuedAt,
            expiresAt: $expiresAt,
        );
    }

    public function revoke(string $keyId): void
    {
        $prefix = (string) config('apiforge.keys.revocation_prefix', 'keys.revoked');
        $cachePrefix = (string) config('apiforge.cache.prefix', 'apiforge');

        $this->cache->forever("{$cachePrefix}.{$prefix}.{$keyId}", true);
    }

    public function isRevoked(string $keyId): bool
    {
        $prefix = (string) config('apiforge.keys.revocation_prefix', 'keys.revoked');
        $cachePrefix = (string) config('apiforge.cache.prefix', 'apiforge');

        return (bool) $this->cache->get("{$cachePrefix}.{$prefix}.{$keyId}", false);
    }
}
