<?php

namespace ApiForge\Facades;

use ApiForge\Support\RegistrySnapshot;
use Illuminate\Support\Facades\Facade;

/**
 * @method static RegistrySnapshot load()
 * @method static RegistrySnapshot all()
 * @method static \ApiForge\Support\EndpointDescriptor|null find(string $method, string $uri, ?string $version = null)
 * @method static RegistrySnapshot reload()
 * @method static void invalidate()
 *
 * @see \ApiForge\Core\ApiRegistry
 */
class ApiForge extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'apiforge.registry';
    }
}
