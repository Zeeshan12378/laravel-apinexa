<?php

namespace ZMJCoder\ApiNexa\Commands;

use ZMJCoder\ApiNexa\Contracts\ApiRegistryContract;
use ZMJCoder\ApiNexa\Contracts\DocumentationGeneratorContract;
use ZMJCoder\ApiNexa\Exceptions\SchemaValidationException;
use Illuminate\Console\Command;

class DocsCommand extends Command
{
    protected $signature = 'apinexa:docs';

    protected $description = 'Generate OpenAPI and HTML documentation from the registry';

    public function handle(
        ApiRegistryContract $registry,
        DocumentationGeneratorContract $documentation,
    ): int {
        try {
            $snapshot = $registry->load();
        } catch (SchemaValidationException $exception) {
            $this->components->error('Cannot generate docs until schemas are valid.');

            foreach ($exception->errors() as $error) {
                $this->line("  • {$error}");
            }

            return self::FAILURE;
        }

        if ($snapshot->count() === 0) {
            $this->components->warn('No endpoints found. Run APINEXA:scan after adding schemas.');

            return self::FAILURE;
        }

        $documentation->generate($snapshot);

        $outputPath = (string) config('apinexa.documentation.output_path');
        $openApi = config('apinexa.documentation.openapi_filename', 'openapi.json');
        $html = config('apinexa.documentation.html_filename', 'index.html');

        $this->components->info('Documentation generated.');
        $this->line("  OpenAPI: {$outputPath}/{$openApi}");
        $this->line("  HTML:    {$outputPath}/{$html}");

        return self::SUCCESS;
    }
}

