<?php

namespace ZMJCoder\ApiNexa\Support;

final readonly class EndpointDescriptor
{
    /**
     * @param  array<string, mixed>  $schema
     * @param  array<int, string>  $roles
     * @param  array<int, string>  $scopes
     * @param  array<string, string>  $payload
     */
    public function __construct(
        public string $key,
        public string $name,
        public string $method,
        public string $endpoint,
        public string $version,
        public bool $auth,
        public array $roles,
        public array $scopes,
        public array $payload,
        public ?string $description,
        public string $sourcePath,
        public array $schema,
    ) {}

    public static function fromSchema(array $schema, string $sourcePath, string $defaultVersion): self
    {
        $method = strtoupper((string) ($schema['method'] ?? 'GET'));
        $endpoint = self::normalizeUri((string) ($schema['endpoint'] ?? '/'));
        $version = (string) ($schema['version'] ?? $defaultVersion);

        return new self(
            key: self::makeKey($version, $method, $endpoint),
            name: (string) ($schema['name'] ?? 'Unnamed Endpoint'),
            method: $method,
            endpoint: $endpoint,
            version: $version,
            auth: (bool) ($schema['auth'] ?? false),
            roles: array_values((array) ($schema['roles'] ?? [])),
            scopes: array_values((array) ($schema['scopes'] ?? [])),
            payload: (array) ($schema['payload'] ?? []),
            description: isset($schema['description']) ? (string) $schema['description'] : null,
            sourcePath: $sourcePath,
            schema: $schema,
        );
    }

    public static function makeKey(string $version, string $method, string $uri): string
    {
        return sprintf('%s:%s:%s', $version, strtoupper($method), self::normalizeUri($uri));
    }

    public static function normalizeUri(string $uri): string
    {
        $uri = '/'.trim($uri, '/');

        return $uri === '/' ? $uri : rtrim($uri, '/');
    }
}

