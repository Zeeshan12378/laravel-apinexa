<?php

namespace ApiForge\Contracts;

use ApiForge\Support\ValidationResult;

interface SchemaValidatorContract
{
    /**
     * @param  array<string, mixed>  $schema
     */
    public function validate(array $schema, ?string $path = null): ValidationResult;
}
