<?php

namespace ZMJCoder\ApiNexa\Support;

final readonly class ValidationResult
{
    /**
     * @param  array<int, string>  $errors
     */
    public function __construct(
        public bool $valid,
        public array $errors = [],
    ) {}

    public static function pass(): self
    {
        return new self(true);
    }

    /**
     * @param  array<int, string>  $errors
     */
    public static function fail(array $errors): self
    {
        return new self(false, $errors);
    }
}

