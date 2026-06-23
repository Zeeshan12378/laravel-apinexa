<?php

namespace ZeeshanMushtaq\ApiNexa\Auth;

use ZeeshanMushtaq\ApiNexa\Contracts\SignatureValidatorContract;
use ZeeshanMushtaq\ApiNexa\Exceptions\InvalidApiKeyException;
use ZeeshanMushtaq\ApiNexa\Support\VerifiedApiKey;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

class SignatureValidator implements SignatureValidatorContract
{
    public function __construct(
        protected CacheRepository $cache,
    ) {}

    public function sign(array $claims): string
    {
        $payload = $this->encodePayload($claims);
        $signature = $this->makeSignature($payload);

        return sprintf(
            'af_%s_%s.%s',
            $claims['mode'] ?? 'live',
            $payload,
            $signature
        );
    }

    public function verify(string $apiKey): VerifiedApiKey
    {
        if (! preg_match('/^af_(live|test)_(.+)\.([a-f0-9]{64})$/', $apiKey, $matches)) {
            throw new InvalidApiKeyException('Malformed API key.');
        }

        [, $mode, $payload, $signature] = $matches;

        if (! hash_equals($this->makeSignature($payload), $signature)) {
            throw new InvalidApiKeyException('Invalid API key signature.');
        }

        $claims = json_decode($this->decodePayload($payload), true);

        if (! is_array($claims)) {
            throw new InvalidApiKeyException('Invalid API key payload.');
        }

        if (($claims['mode'] ?? null) !== $mode) {
            throw new InvalidApiKeyException('API key mode mismatch.');
        }

        $expiresAt = (int) ($claims['exp'] ?? 0);

        if ($expiresAt > 0 && $expiresAt < time()) {
            throw new InvalidApiKeyException('API key has expired.');
        }

        $keyId = (string) ($claims['kid'] ?? '');

        if ($keyId !== '' && $this->isRevoked($keyId)) {
            throw new InvalidApiKeyException('API key has been revoked.');
        }

        return new VerifiedApiKey(
            keyId: $keyId,
            mode: $mode,
            scopes: array_values((array) ($claims['scopes'] ?? [])),
            permissions: array_values((array) ($claims['permissions'] ?? [])),
            issuedAt: (int) ($claims['iat'] ?? 0),
            expiresAt: $expiresAt,
            claims: $claims,
        );
    }

    protected function makeSignature(string $payload): string
    {
        return hash_hmac('sha256', $payload, $this->signingKey());
    }

    /**
     * @param  array<string, mixed>  $claims
     */
    protected function encodePayload(array $claims): string
    {
        $json = json_encode($claims, JSON_UNESCAPED_SLASHES);

        return rtrim(strtr(base64_encode($json ?: '{}'), '+/', '-_'), '=');
    }

    protected function decodePayload(string $payload): string
    {
        $decoded = base64_decode(strtr($payload, '-_', '+/'), true);

        return $decoded === false ? '{}' : $decoded;
    }

    protected function signingKey(): string
    {
        $key = config('apinexa.keys.signing_key');

        if (is_string($key) && $key !== '') {
            return $key;
        }

        $appKey = (string) config('app.key');

        if (str_starts_with($appKey, 'base64:')) {
            $decoded = base64_decode(substr($appKey, 7), true);

            if ($decoded !== false) {
                return $decoded;
            }
        }

        return $appKey;
    }

    protected function isRevoked(string $keyId): bool
    {
        $prefix = (string) config('apinexa.keys.revocation_prefix', 'keys.revoked');
        $cachePrefix = (string) config('apinexa.cache.prefix', 'APINEXA');

        return (bool) $this->cache->get("{$cachePrefix}.{$prefix}.{$keyId}", false);
    }
}

