<?php

namespace ApiForge\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'apiforge:install';

    protected $description = 'Publish ApiForge configuration and scaffold schema directories';

    public function handle(): int
    {
        $this->call('vendor:publish', [
            '--tag' => 'apiforge-config',
            '--force' => false,
        ]);

        $schemaPath = base_path('api-forge/schemas');
        $docsPath = base_path('api-forge/docs');

        if (! is_dir($schemaPath)) {
            mkdir($schemaPath, 0755, true);
            $this->components->info('Created api-forge/schemas directory.');
        }

        if (! is_dir($docsPath)) {
            mkdir($docsPath, 0755, true);
            $this->components->info('Created api-forge/docs directory.');
        }

        $examplePath = $schemaPath.'/example.php';

        if (! file_exists($examplePath)) {
            $stub = file_get_contents(__DIR__.'/../../stubs/schemas/example.php');

            if ($stub !== false) {
                file_put_contents($examplePath, $stub);
                $this->components->info('Created example schema at api-forge/schemas/example.php.');
            }
        }

        $this->newLine();
        $this->components->info('ApiForge installed successfully.');
        $this->line('Next steps:');
        $this->line('  1. Add schema files to api-forge/schemas/');
        $this->line('  2. php artisan apiforge:scan');
        $this->line('  3. php artisan apiforge:docs');

        return self::SUCCESS;
    }
}
