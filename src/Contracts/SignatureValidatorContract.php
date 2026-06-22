<?php

namespace ApiForge\Contracts;

use ApiForge\Support\VerifiedApiKey;

interface SignatureValidatorContract
{
    /**
     * @param  array<string, mixed>  $claims
     */
    public function sign(array $claims): string;

    public function verify(string $apiKey): VerifiedApiKey;
}
