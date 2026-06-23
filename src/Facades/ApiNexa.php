<?php

namespace ZeeshanMushtaq\ApiNexa\Facades;

use ZeeshanMushtaq\ApiNexa\Support\RegistrySnapshot;
use Illuminate\Support\Facades\Facade;

/**
 * @method static RegistrySnapshot load()
 * @method static RegistrySnapshot all()
 * @method static \APINEXA\Support\EndpointDescriptor|null find(string $method, string $uri, ?string $version = null)
 * @method static RegistrySnapshot reload()
 * @method static void invalidate()
 *
 * @see \APINEXA\Core\ApiRegistry
 */
class ApiNexa extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'apinexa.registry';
    }
}

