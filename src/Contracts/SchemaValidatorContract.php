<?php

namespace ZeeshanMushtaq\ApiNexa\Contracts;

use ZeeshanMushtaq\ApiNexa\Support\ValidationResult;

interface SchemaValidatorContract
{
    /**
     * @param  array<string, mixed>  $schema
     */
    public function validate(array $schema, ?string $path = null): ValidationResult;
}

