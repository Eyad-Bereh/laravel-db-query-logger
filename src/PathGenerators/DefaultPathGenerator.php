<?php

namespace EyadBereh\LaravelDbQueryLogger\PathGenerators;

use EyadBereh\LaravelDbQueryLogger\Interfaces\PathGeneratorInterface;
use Illuminate\Support\Facades\Storage;

class DefaultPathGenerator implements PathGeneratorInterface
{

    public function path(): string
    {
        return 'db-query-logger';
    }
}