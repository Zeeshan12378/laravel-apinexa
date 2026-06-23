<?php

namespace ZMJCoder\ApiNexa\Contracts;

interface SchemaLoaderContract
{
    public function supports(string $path): bool;

    /**
     * @return array<string, mixed>
     */
    public function load(string $path): array;

    /**
     * @return array<int, string>
     */
    public function discover(): array;
}

