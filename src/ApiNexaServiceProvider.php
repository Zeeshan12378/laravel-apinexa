<?php

namespace ZeeshanMushtaq\ApiNexa;

use ZeeshanMushtaq\ApiNexa\Auth\ApiKeyManager;
use ZeeshanMushtaq\ApiNexa\Auth\SignatureValidator;
use ZeeshanMushtaq\ApiNexa\Commands\DocsCommand;
use ZeeshanMushtaq\ApiNexa\Commands\InstallCommand;
use ZeeshanMushtaq\ApiNexa\Commands\ScanCommand;
use ZeeshanMushtaq\ApiNexa\Contracts\ApiKeyManagerContract;
use ZeeshanMushtaq\ApiNexa\Contracts\ApiRegistryContract;
use ZeeshanMushtaq\ApiNexa\Contracts\DocumentationGeneratorContract;
use ZeeshanMushtaq\ApiNexa\Contracts\SchemaLoaderContract;
use ZeeshanMushtaq\ApiNexa\Contracts\SchemaValidatorContract;
use ZeeshanMushtaq\ApiNexa\Contracts\SignatureValidatorContract;
use ZeeshanMushtaq\ApiNexa\Core\ApiRegistry;
use ZeeshanMushtaq\ApiNexa\Core\SchemaLoader;
use ZeeshanMushtaq\ApiNexa\Core\SchemaValidator;
use ZeeshanMushtaq\ApiNexa\Documentation\DocumentationGenerator;
use ZeeshanMushtaq\ApiNexa\Middleware\ApiKeyMiddleware;
use Illuminate\Cache\CacheManager;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class ApiNexaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/apinexa.php', 'APINEXA');

        $this->app->singleton(SchemaLoaderContract::class, SchemaLoader::class);
        $this->app->singleton(SchemaValidatorContract::class, SchemaValidator::class);
        $this->app->singleton(ApiRegistryContract::class, ApiRegistry::class);
        $this->app->singleton(SignatureValidatorContract::class, SignatureValidator::class);
        $this->app->singleton(ApiKeyManagerContract::class, ApiKeyManager::class);
        $this->app->singleton(DocumentationGeneratorContract::class, DocumentationGenerator::class);

        $this->app->when([ApiRegistry::class, SignatureValidator::class, ApiKeyManager::class])
            ->needs(CacheRepository::class)
            ->give(fn () => $this->resolveCacheStore());

        $this->app->alias(ApiRegistryContract::class, 'apinexa.registry');
    }

    protected function resolveCacheStore(): CacheRepository
    {
        /** @var CacheManager $cache */
        $cache = $this->app->make('cache');
        $store = config('apinexa.cache.store');

        return $store ? $cache->store($store) : $cache->store();
    }

    public function boot(): void
    {
        if (! config('apinexa.enabled', true)) {
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
                __DIR__.'/../config/apinexa.php' => config_path('apinexa.php'),
            ], 'apinexa-config');

            $this->publishes([
                __DIR__.'/../stubs/schemas/example.php' => base_path('api-nexa/schemas/example.php'),
            ], 'apinexa-schemas');
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
            config('apinexa.middleware.alias', 'apinexa.key'),
            ApiKeyMiddleware::class
        );
    }

    protected function bootRegistry(): void
    {
        if (config('apinexa.schemas.hot_reload', false)) {
            return;
        }

        if (! config('apinexa.cache.enabled', true)) {
            return;
        }

        $this->app->booted(function () {
            /** @var ApiRegistryContract $registry */
            $registry = $this->app->make(ApiRegistryContract::class);
            $registry->load();
        });
    }
}

