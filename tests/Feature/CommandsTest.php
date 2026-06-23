<?php

namespace ZMJCoder\ApiNexa\Tests\Feature;

use ZMJCoder\ApiNexa\Contracts\ApiRegistryContract;
use ZMJCoder\ApiNexa\Contracts\DocumentationGeneratorContract;
use ZMJCoder\ApiNexa\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;

class CommandsTest extends TestCase
{
    public function test_scan_command_compiles_registry(): void
    {
        Artisan::call('APINEXA:scan');

        $this->assertStringContainsString('Scanned 2 endpoint(s)', Artisan::output());
    }

    public function test_docs_command_generates_files(): void
    {
        Artisan::call('APINEXA:scan');
        Artisan::call('APINEXA:docs');

        $outputPath = config('apinexa.documentation.output_path');

        $this->assertFileExists($outputPath.'/openapi.json');
        $this->assertFileExists($outputPath.'/index.html');

        $openApi = json_decode(file_get_contents($outputPath.'/openapi.json'), true);

        $this->assertSame('3.1.0', $openApi['openapi']);
        $this->assertArrayHasKey('/jobs', $openApi['paths']);
    }

    public function test_documentation_uses_registry_snapshot(): void
    {
        $registry = $this->app->make(ApiRegistryContract::class);
        $documentation = $this->app->make(DocumentationGeneratorContract::class);

        $snapshot = $registry->reload();
        $openApi = $documentation->toOpenApi($snapshot);

        $this->assertArrayHasKey('post', $openApi['paths']['/jobs']);
    }
}

