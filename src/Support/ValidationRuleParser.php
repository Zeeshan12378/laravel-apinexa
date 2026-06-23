<?php

namespace ZeeshanMushtaq\ApiNexa\Support;

final class ValidationRuleParser
{
    /**
     * Parse Laravel-style validation rule strings into OpenAPI property schema.
     *
     * @return array<string, mixed>
     */
    public static function toOpenApiProperty(string $rules): array
    {
        $parts = array_map('trim', explode('|', $rules));
        $schema = ['type' => 'string'];

        foreach ($parts as $part) {
            if ($part === 'required') {
                continue;
            }

            if ($part === 'nullable') {
                $schema['nullable'] = true;

                continue;
            }

            if (in_array($part, ['string', 'numeric', 'integer', 'boolean', 'array', 'object'], true)) {
                $schema['type'] = $part === 'numeric' ? 'number' : $part;

                continue;
            }

            if (str_starts_with($part, 'max:')) {
                $schema['maxLength'] ??= (int) substr($part, 4);

                continue;
            }

            if (str_starts_with($part, 'min:')) {
                $schema['minLength'] ??= (int) substr($part, 4);

                continue;
            }

            if (str_starts_with($part, 'email')) {
                $schema['format'] = 'email';
            }
        }

        return $schema;
    }

    /**
     * @param  array<string, string>  $payload
     * @return array<int, string>
     */
    public static function requiredFields(array $payload): array
    {
        $required = [];

        foreach ($payload as $field => $rules) {
            if (str_contains((string) $rules, 'required')) {
                $required[] = $field;
            }
        }

        return $required;
    }
}

