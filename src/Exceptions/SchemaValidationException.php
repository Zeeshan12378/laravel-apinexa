<?php

namespace ZeeshanMushtaq\ApiNexa\Exceptions;

use RuntimeException;

class SchemaValidationException extends RuntimeException
{
    /**
     * @param  array<int, string>  $errors
     */
    public function __construct(
        protected array $errors,
    ) {
        parent::__construct('Schema validation failed: '.implode(' ', $errors));
    }

    /**
     * @return array<int, string>
     */
    public function errors(): array
    {
        return $this->errors;
    }
}

