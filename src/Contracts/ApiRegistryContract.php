<?php

namespace ZeeshanMushtaq\ApiNexa\Contracts;

use ZeeshanMushtaq\ApiNexa\Support\RegistrySnapshot;
use ZeeshanMushtaq\ApiNexa\Support\EndpointDescriptor;

interface ApiRegistryContract
{
    public function load(): RegistrySnapshot;

    public function all(): RegistrySnapshot;

    public function find(string $method, string $uri, ?string $version = null): ?EndpointDescriptor;

    public function reload(): RegistrySnapshot;

    public function invalidate(): void;
}

