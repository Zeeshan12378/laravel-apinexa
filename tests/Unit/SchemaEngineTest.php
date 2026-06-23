<?php

namespace ZMJCoder\ApiNexa\Tests\Unit;

use ZMJCoder\ApiNexa\Contracts\SchemaLoaderContract;
use ZMJCoder\ApiNexa\Contracts\SchemaValidatorContract;
use ZMJCoder\ApiNexa\Core\ApiRegistry;
use ZMJCoder\ApiNexa\Core\SchemaLoader;
use ZMJCoder\ApiNexa\Core\SchemaValidator;
use ZMJCoder\ApiNexa\Tests\TestCase;

class SchemaEngineTest extends TestCase
{
    public function test_it_discovers_and_validates_schemas(): void
    {
        $loader = $this->app->make(SchemaLoaderContract::class);

        $this->assertInstanceOf(SchemaLoader::class, $loader);
        $this->assertCount(2, $loader->discover());

        $schema = $loader->load(__DIR__.'/../fixtures/schemas/create-job.php');

        $validator = $this->app->make(SchemaValidatorContract::class);
        $result = $validator->validate($schema);

        $this->assertTrue($result->valid);
    }

    public function test_registry_compiles_endpoints(): void
    {
        $registry = $this->app->make(ApiRegistry::class);
        $snapshot = $registry->reload();

        $this->assertSame(2, $snapshot->count());
        $this->assertNotNull($snapshot->endpoints['v1:POST:/jobs']);
        $this->assertNotNull($snapshot->endpoints['v1:GET:/jobs']);
    }

    public function test_validator_rejects_invalid_schema(): void
    {
        $validator = new SchemaValidator;

        $result = $validator->validate([
            'name' => 'Broken',
        ], 'broken.php');

        $this->assertFalse($result->valid);
        $this->assertNotEmpty($result->errors);
    }
}

