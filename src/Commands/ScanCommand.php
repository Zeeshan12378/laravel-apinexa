<?php

namespace ZMJCoder\ApiNexa\Commands;

use ZMJCoder\ApiNexa\Contracts\ApiRegistryContract;
use ZMJCoder\ApiNexa\Exceptions\SchemaValidationException;
use Illuminate\Console\Command;

class ScanCommand extends Command
{
    protected $signature = 'apinexa:scan {--force : Rebuild the registry even if cache is valid}';

    protected $description = 'Discover and compile API schemas into the runtime registry';

    public function handle(ApiRegistryContract $registry): int
    {
        if ($this->option('force')) {
            $registry->invalidate();
        }

        try {
            $snapshot = $registry->reload();
        } catch (SchemaValidationException $exception) {
            $this->components->error('Schema validation failed.');

            foreach ($exception->errors() as $error) {
                $this->line("  • {$error}");
            }

            return self::FAILURE;
        }

        $this->components->info("Scanned {$snapshot->count()} endpoint(s).");
        $this->line("Compiled at: {$snapshot->compiledAt}");

        foreach ($snapshot->endpoints as $endpoint) {
            $this->line("  [{$endpoint->method}] {$endpoint->endpoint} — {$endpoint->name}");
        }

        return self::SUCCESS;
    }
}

