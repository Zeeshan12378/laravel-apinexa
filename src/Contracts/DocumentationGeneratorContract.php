<?php

namespace ZeeshanMushtaq\ApiNexa\Contracts;

use ZeeshanMushtaq\ApiNexa\Support\RegistrySnapshot;

interface DocumentationGeneratorContract
{
    public function generate(RegistrySnapshot $registry): void;

    /**
     * @return array<string, mixed>
     */
    public function toOpenApi(RegistrySnapshot $registry): array;

    public function toHtml(RegistrySnapshot $registry): string;
}

