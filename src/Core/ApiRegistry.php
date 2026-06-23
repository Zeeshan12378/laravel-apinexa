<?php

namespace ZMJCoder\ApiNexa\Core;

use ZMJCoder\ApiNexa\Contracts\ApiRegistryContract;
use ZMJCoder\ApiNexa\Contracts\SchemaLoaderContract;
use ZMJCoder\ApiNexa\Contracts\SchemaValidatorContract;
use ZMJCoder\ApiNexa\Exceptions\SchemaValidationException;
use ZMJCoder\ApiNexa\Support\ConfigHash;
use ZMJCoder\ApiNexa\Support\EndpointDescriptor;
use ZMJCoder\ApiNexa\Support\RegistrySnapshot;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Filesystem\Filesystem;

class ApiRegistry implements ApiRegistryContract
{
    protected ?RegistrySnapshot $memory = null;

    public function __construct(
        protected SchemaLoaderContract $loader,
        protected SchemaValidatorContract $validator,
        protected CacheRepository $cache,
        protected Filesystem $files,
    ) {}

    public function load(): RegistrySnapshot
    {
        if ($this->memory !== null && ! config('apinexa.schemas.hot_reload', false)) {
            return $this->memory;
        }

        if (config('apinexa.cache.enabled', true)) {
            $cached = $this->readFromCache();

            if ($cached !== null) {
                $this->memory = $cached;

                return $cached;
            }
        }

        return $this->reload();
    }

    public function all(): RegistrySnapshot
    {
        return $this->load();
    }

    public function find(string $method, string $uri, ?string $version = null): ?EndpointDescriptor
    {
        $version ??= (string) config('apinexa.default_version', 'v1');
        $key = EndpointDescriptor::makeKey($version, $method, $uri);

        return $this->load()->endpoints[$key] ?? null;
    }

    public function reload(): RegistrySnapshot
    {
        $defaultVersion = (string) config('apinexa.default_version', 'v1');
        $endpoints = [];
        $fileHashes = [];
        $errors = [];

        foreach ($this->loader->discover() as $path) {
            $fileHashes[$path] = hash_file('sha256', $path) ?: '';

            try {
                $schema = $this->loader->load($path);
            } catch (\Throwable $exception) {
                $errors[] = "{$path}: {$exception->getMessage()}";

                continue;
            }

            $result = $this->validator->validate($schema, $path);

            if (! $result->valid) {
                $errors = array_merge($errors, $result->errors);

                continue;
            }

            $descriptor = EndpointDescriptor::fromSchema($schema, $path, $defaultVersion);
            $endpoints[$descriptor->key] = $descriptor;
        }

        if ($errors !== []) {
            throw new SchemaValidationException($errors);
        }

        $snapshot = new RegistrySnapshot(
            compiledAt: now()->toIso8601String(),
            configHash: ConfigHash::make(),
            endpoints: $endpoints,
            fileHashes: $fileHashes,
        );

        $this->memory = $snapshot;
        $this->writeToCache($snapshot);

        return $snapshot;
    }

    public function invalidate(): void
    {
        $this->memory = null;
        $this->cache->forget($this->cacheKey());
    }

    protected function readFromCache(): ?RegistrySnapshot
    {
        $payload = $this->cache->get($this->cacheKey());

        if (! is_array($payload)) {
            return null;
        }

        if (($payload['config_hash'] ?? '') !== ConfigHash::make()) {
            return null;
        }

        $currentHashes = $this->collectFileHashes();

        if (($payload['file_hashes'] ?? []) !== $currentHashes) {
            return null;
        }

        return RegistrySnapshot::fromArray($payload);
    }

    protected function writeToCache(RegistrySnapshot $snapshot): void
    {
        if (! config('apinexa.cache.enabled', true)) {
            return;
        }

        $this->cache->put(
            $this->cacheKey(),
            $snapshot->toArray(),
            (int) config('apinexa.cache.ttl', 86400)
        );
    }

    protected function cacheKey(): string
    {
        $prefix = (string) config('apinexa.cache.prefix', 'APINEXA');

        return "{$prefix}.".config('apinexa.cache.registry_key', 'registry');
    }

    /**
     * @return array<string, string>
     */
    protected function collectFileHashes(): array
    {
        $hashes = [];

        foreach ($this->loader->discover() as $path) {
            $hashes[$path] = hash_file('sha256', $path) ?: '';
        }

        return $hashes;
    }
}

