<?php

namespace ApiForge\Core;

use ApiForge\Contracts\SchemaLoaderContract;
use Illuminate\Filesystem\Filesystem;

class SchemaLoader implements SchemaLoaderContract
{
    public function __construct(
        protected Filesystem $files,
    ) {}

    public function supports(string $path): bool
    {
        return str_ends_with(strtolower($path), '.php') && $this->files->isFile($path);
    }

    public function load(string $path): array
    {
        if (! $this->supports($path)) {
            throw new \InvalidArgumentException("Unsupported schema file: {$path}");
        }

        $schema = require $path;

        if (! is_array($schema)) {
            throw new \RuntimeException("Schema file must return an array: {$path}");
        }

        return $schema;
    }

    public function discover(): array
    {
        $paths = config('apiforge.schemas.paths', []);
        $pattern = config('apiforge.schemas.pattern', '*.php');
        $discovered = [];

        foreach ($paths as $directory) {
            if (! $this->files->isDirectory($directory)) {
                continue;
            }

            foreach ($this->files->glob(rtrim($directory, '\\/').DIRECTORY_SEPARATOR.$pattern) ?: [] as $file) {
                if ($this->supports($file)) {
                    $discovered[] = $file;
                }
            }
        }

        sort($discovered);

        return $discovered;
    }
}
