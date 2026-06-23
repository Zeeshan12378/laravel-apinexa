<?php

namespace ZeeshanMushtaq\ApiNexa\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'apinexa:install';

    protected $description = 'Publish APINEXA configuration and scaffold schema directories';

    public function handle(): int
    {
        $this->call('vendor:publish', [
            '--tag' => 'apinexa-config',
            '--force' => false,
        ]);

        $schemaPath = base_path('api-nexa/schemas');
        $docsPath = base_path('api-nexa/docs');

        if (! is_dir($schemaPath)) {
            mkdir($schemaPath, 0755, true);
            $this->components->info('Created api-nexa/schemas directory.');
        }

        if (! is_dir($docsPath)) {
            mkdir($docsPath, 0755, true);
            $this->components->info('Created api-nexa/docs directory.');
        }

        $examplePath = $schemaPath.'/example.php';

        if (! file_exists($examplePath)) {
            $stub = file_get_contents(__DIR__.'/../../stubs/schemas/example.php');

            if ($stub !== false) {
                file_put_contents($examplePath, $stub);
                $this->components->info('Created example schema at api-nexa/schemas/example.php.');
            }
        }

        $this->newLine();
        $this->components->info('ApiNexa installed successfully.');
        $this->line('Next steps:');
        $this->line('  Option A - Auto-discover routes:');
        $this->line('    1. php artisan apinexa:discover');
        $this->line('    2. php artisan apinexa:docs');
        $this->line('');
        $this->line('  Option B - Manual schemas:');
        $this->line('    1. Add schema files to api-nexa/schemas/');
        $this->line('    2. php artisan apinexa:scan');
        $this->line('    3. php artisan apinexa:docs');

        return self::SUCCESS;
    }
}

