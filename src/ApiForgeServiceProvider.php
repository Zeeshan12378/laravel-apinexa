<?php

namespace ApiForge;

use ApiForge\Auth\ApiKeyManager;
use ApiForge\Auth\SignatureValidator;
use ApiForge\Commands\DocsCommand;
use ApiForge\Commands\InstallCommand;
use ApiForge\Commands\ScanCommand;
use ApiForge\Contracts\ApiKeyManagerContract;
use ApiForge\Contracts\ApiRegistryContract;
use ApiForge\Contracts\DocumentationGeneratorContract;
use ApiForge\Contracts\SchemaLoaderContract;
use ApiForge\Contracts\SchemaValidatorContract;
use ApiForge\Contracts\SignatureValidatorContract;
use ApiForge\Core\ApiRegistry;
use ApiForge\Core\SchemaLoader;
use ApiForge\Core\SchemaValidator;
use ApiForge\Documentation\DocumentationGenerator;
use ApiForge\Middleware\ApiKeyMiddleware;
use Illuminate\Cache\CacheManager;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class ApiForgeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/apiforge.php', 'apiforge');

        $this->app->singleton(SchemaLoaderContract::class, SchemaLoader::class);
        $this->app->singleton(SchemaValidatorContract::class, SchemaValidator::class);
        $this->app->singleton(ApiRegistryContract::class, ApiRegistry::class);
        $this->app->singleton(SignatureValidatorContract::class, SignatureValidator::class);
        $this->app->singleton(ApiKeyManagerContract::class, ApiKeyManager::class);
        $this->app->singleton(DocumentationGeneratorContract::class, DocumentationGenerator::class);

        $this->app->when([ApiRegistry::class, SignatureValidator::class, ApiKeyManager::class])
            ->needs(CacheRepository::class)
            ->give(fn () => $this->resolveCacheStore());

        $this->app->alias(ApiRegistryContract::class, 'apiforge.registry');
    }

    protected function resolveCacheStore(): CacheRepository
    {
        /** @var CacheManager $cache */
        $cache = $this->app->make('cache');
        $store = config('apiforge.cache.store');

        return $store ? $cache->store($store) : $cache->store();
    }

    public function boot(): void
    {
        if (! config('apiforge.enabled', true)) {
            return;
        }

        $this->registerPublishing();
        $this->registerCommands();
        $this->registerMiddleware();
        $this->bootRegistry();
    }

    protected function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/apiforge.php' => config_path('apiforge.php'),
            ], 'apiforge-config');

            $this->publishes([
                __DIR__.'/../stubs/schemas/example.php' => base_path('api-forge/schemas/example.php'),
            ], 'apiforge-schemas');
        }
    }

    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                ScanCommand::class,
                DocsCommand::class,
            ]);
        }
    }

    protected function registerMiddleware(): void
    {
        /** @var Router $router */
        $router = $this->app->make(Router::class);

        $router->aliasMiddleware(
            config('apiforge.middleware.alias', 'apiforge.key'),
            ApiKeyMiddleware::class
        );
    }

    protected function bootRegistry(): void
    {
        if (config('apiforge.schemas.hot_reload', false)) {
            return;
        }

        if (! config('apiforge.cache.enabled', true)) {
            return;
        }

        $this->app->booted(function () {
            /** @var ApiRegistryContract $registry */
            $registry = $this->app->make(ApiRegistryContract::class);
            $registry->load();
        });
    }
}
