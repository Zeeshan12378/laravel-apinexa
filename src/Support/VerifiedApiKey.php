<?php

namespace ApiForge\Support;

final readonly class VerifiedApiKey
{
    /**
     * @param  array<string, mixed>  $claims
     * @param  array<int, string>  $scopes
     * @param  array<int, string>  $permissions
     */
    public function __construct(
        public string $keyId,
        public string $mode,
        public array $scopes,
        public array $permissions,
        public int $issuedAt,
        public int $expiresAt,
        public array $claims = [],
    ) {}

    public function hasScope(string $scope): bool
    {
        return in_array($scope, $this->scopes, true) || in_array('*', $this->scopes, true);
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions, true) || in_array('*', $this->permissions, true);
    }
}
