<?php

namespace ApiForge\Contracts;

use ApiForge\Support\RegistrySnapshot;

interface ApiRegistryContract
{
    public function load(): RegistrySnapshot;

    public function all(): RegistrySnapshot;

    public function find(string $method, string $uri, ?string $version = null): ?\ApiForge\Support\EndpointDescriptor;

    public function reload(): RegistrySnapshot;

    public function invalidate(): void;
}
