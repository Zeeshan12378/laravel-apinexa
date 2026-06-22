<?php

namespace ApiForge\Documentation;

use ApiForge\Contracts\DocumentationGeneratorContract;
use ApiForge\Support\RegistrySnapshot;
use Illuminate\Filesystem\Filesystem;

class DocumentationGenerator implements DocumentationGeneratorContract
{
    public function __construct(
        protected OpenApiGenerator $openApiGenerator,
        protected HtmlGenerator $htmlGenerator,
        protected Filesystem $files,
    ) {}

    public function generate(RegistrySnapshot $registry): void
    {
        $outputPath = (string) config('apiforge.documentation.output_path');

        if (! $this->files->isDirectory($outputPath)) {
            $this->files->makeDirectory($outputPath, 0755, true);
        }

        $openApiPath = $outputPath.DIRECTORY_SEPARATOR.config('apiforge.documentation.openapi_filename', 'openapi.json');
        $htmlPath = $outputPath.DIRECTORY_SEPARATOR.config('apiforge.documentation.html_filename', 'index.html');

        $this->files->put(
            $openApiPath,
            json_encode($this->toOpenApi($registry), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL
        );

        $this->files->put($htmlPath, $this->toHtml($registry));
    }

    public function toOpenApi(RegistrySnapshot $registry): array
    {
        return $this->openApiGenerator->generate($registry);
    }

    public function toHtml(RegistrySnapshot $registry): string
    {
        return $this->htmlGenerator->generate($registry);
    }
}
