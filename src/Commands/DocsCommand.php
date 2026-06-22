<?php

namespace ApiForge\Commands;

use ApiForge\Contracts\ApiRegistryContract;
use ApiForge\Contracts\DocumentationGeneratorContract;
use ApiForge\Exceptions\SchemaValidationException;
use Illuminate\Console\Command;

class DocsCommand extends Command
{
    protected $signature = 'apiforge:docs';

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
            $this->components->warn('No endpoints found. Run apiforge:scan after adding schemas.');

            return self::FAILURE;
        }

        $documentation->generate($snapshot);

        $outputPath = (string) config('apiforge.documentation.output_path');
        $openApi = config('apiforge.documentation.openapi_filename', 'openapi.json');
        $html = config('apiforge.documentation.html_filename', 'index.html');

        $this->components->info('Documentation generated.');
        $this->line("  OpenAPI: {$outputPath}/{$openApi}");
        $this->line("  HTML:    {$outputPath}/{$html}");

        return self::SUCCESS;
    }
}
