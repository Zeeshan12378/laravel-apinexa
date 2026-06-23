<?php

namespace ZeeshanMushtaq\ApiNexa\Core;

use ZeeshanMushtaq\ApiNexa\Contracts\SchemaValidatorContract;
use ZeeshanMushtaq\ApiNexa\Support\ValidationResult;

class SchemaValidator implements SchemaValidatorContract
{
    /** @var array<int, string> */
    protected array $requiredFields = ['name', 'method', 'endpoint'];

    /** @var array<int, string> */
    protected array $allowedMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'HEAD'];

    public function validate(array $schema, ?string $path = null): ValidationResult
    {
        $errors = [];
        $location = $path ? " in [{$path}]" : '';

        foreach ($this->requiredFields as $field) {
            if (! array_key_exists($field, $schema) || $schema[$field] === '' || $schema[$field] === null) {
                $errors[] = "Missing required field '{$field}'{$location}.";
            }
        }

        if (isset($schema['method'])) {
            $method = strtoupper((string) $schema['method']);

            if (! in_array($method, $this->allowedMethods, true)) {
                $errors[] = "Invalid HTTP method '{$schema['method']}'{$location}.";
            }
        }

        if (isset($schema['endpoint']) && ! is_string($schema['endpoint'])) {
            $errors[] = "Field 'endpoint' must be a string{$location}.";
        }

        if (isset($schema['payload']) && ! is_array($schema['payload'])) {
            $errors[] = "Field 'payload' must be an array{$location}.";
        }

        if (isset($schema['roles']) && ! is_array($schema['roles'])) {
            $errors[] = "Field 'roles' must be an array{$location}.";
        }

        if (isset($schema['scopes']) && ! is_array($schema['scopes'])) {
            $errors[] = "Field 'scopes' must be an array{$location}.";
        }

        if (isset($schema['auth']) && ! is_bool($schema['auth'])) {
            $errors[] = "Field 'auth' must be a boolean{$location}.";
        }

        return $errors === [] ? ValidationResult::pass() : ValidationResult::fail($errors);
    }
}

