<?php

namespace ZeeshanMushtaq\ApiNexa\Contracts;

use ZeeshanMushtaq\ApiNexa\Support\VerifiedApiKey;

interface SignatureValidatorContract
{
    /**
     * @param  array<string, mixed>  $claims
     */
    public function sign(array $claims): string;

    public function verify(string $apiKey): VerifiedApiKey;
}

