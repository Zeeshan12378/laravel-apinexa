<?php

namespace ZMJCoder\ApiNexa\Contracts;

use ZMJCoder\ApiNexa\Support\RegistrySnapshot;

interface ApiRegistryContract
{
    public function load(): RegistrySnapshot;

    public function all(): RegistrySnapshot;

    public function find(string $method, string $uri, ?string $version = null): ?\APINEXA\Support\EndpointDescriptor;

    public function reload(): RegistrySnapshot;

    public function invalidate(): void;
}

