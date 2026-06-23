<?php

namespace ZeeshanMushtaq\ApiNexa\Support;

final readonly class RegistrySnapshot
{
    /**
     * @param  array<string, EndpointDescriptor>  $endpoints  keyed by endpoint key
     * @param  array<string, string>  $fileHashes
     */
    public function __construct(
        public string $compiledAt,
        public string $configHash,
        public array $endpoints,
        public array $fileHashes = [],
    ) {}

    public function count(): int
    {
        return count($this->endpoints);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'compiled_at' => $this->compiledAt,
            'config_hash' => $this->configHash,
            'count' => $this->count(),
            'file_hashes' => $this->fileHashes,
            'endpoints' => array_map(
                fn (EndpointDescriptor $endpoint) => [
                    'key' => $endpoint->key,
                    'name' => $endpoint->name,
                    'method' => $endpoint->method,
                    'endpoint' => $endpoint->endpoint,
                    'version' => $endpoint->version,
                    'auth' => $endpoint->auth,
                    'roles' => $endpoint->roles,
                    'scopes' => $endpoint->scopes,
                    'payload' => $endpoint->payload,
                    'description' => $endpoint->description,
                    'source_path' => $endpoint->sourcePath,
                ],
                $this->endpoints
            ),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data, array $schemasByPath = []): self
    {
        $endpoints = [];

        foreach ($data['endpoints'] ?? [] as $item) {
            $sourcePath = (string) ($item['source_path'] ?? '');
            $schema = $schemasByPath[$sourcePath] ?? array_merge($item, [
                'name' => $item['name'] ?? 'Unnamed',
                'method' => $item['method'] ?? 'GET',
                'endpoint' => $item['endpoint'] ?? '/',
            ]);

            $descriptor = EndpointDescriptor::fromSchema(
                $schema,
                $sourcePath,
                (string) ($item['version'] ?? config('apinexa.default_version', 'v1'))
            );

            $endpoints[$descriptor->key] = $descriptor;
        }

        return new self(
            compiledAt: (string) ($data['compiled_at'] ?? now()->toIso8601String()),
            configHash: (string) ($data['config_hash'] ?? ''),
            endpoints: $endpoints,
            fileHashes: (array) ($data['file_hashes'] ?? []),
        );
    }
}

