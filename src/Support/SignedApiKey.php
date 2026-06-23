<?php

namespace ZMJCoder\ApiNexa\Support;

final readonly class SignedApiKey
{
    /**
     * @param  array<int, string>  $scopes
     * @param  array<int, string>  $permissions
     */
    public function __construct(
        public string $key,
        public string $keyId,
        public string $mode,
        public array $scopes,
        public array $permissions,
        public int $issuedAt,
        public int $expiresAt,
    ) {}
}

