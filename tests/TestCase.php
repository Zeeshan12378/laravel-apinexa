<?php

namespace ApiForge\Tests;

use ApiForge\ApiForgeServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [ApiForgeServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('apiforge.enabled', true);
        $app['config']->set('apiforge.cache.enabled', false);
        $app['config']->set('apiforge.schemas.hot_reload', true);
        $app['config']->set('apiforge.schemas.paths', [
            __DIR__.'/fixtures/schemas',
        ]);
        $app['config']->set('apiforge.documentation.output_path', __DIR__.'/output/docs');
        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));
    }

    protected function tearDown(): void
    {
        $output = __DIR__.'/output';

        if (is_dir($output)) {
            $this->deleteDirectory($output);
        }

        parent::tearDown();
    }

    protected function deleteDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        foreach (scandir($directory) ?: [] as $item) {
            if (in_array($item, ['.', '..'], true)) {
                continue;
            }

            $path = $directory.DIRECTORY_SEPARATOR.$item;

            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($directory);
    }
}
