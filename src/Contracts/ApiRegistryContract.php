<?php

namespace ZeeshanMushtaq\ApiNexa\Contracts;

use ZeeshanMushtaq\ApiNexa\Support\RegistrySnapshot;

interface ApiRegistryContract
{
    public function load(): RegistrySnapshot;

    public function all(): RegistrySnapshot;

    public function find(string $method, string $uri, ?string $version = null): ?\APINEXA\Support\EndpointDescriptor;

    public function reload(): RegistrySnapshot;

    public function invalidate(): void;
}

