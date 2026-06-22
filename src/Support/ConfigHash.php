<?php

namespace ApiForge\Support;

use Illuminate\Support\Arr;

final class ConfigHash
{
    public static function make(): string
    {
        return hash('sha256', serialize(Arr::sortRecursive(config('apiforge', []))));
    }
}
