<?php

namespace EyadBereh\LaravelDbQueryLogger\PathGenerators;

use EyadBereh\LaravelDbQueryLogger\Interfaces\PathGeneratorInterface;

class DefaultPathGenerator implements PathGeneratorInterface
{
    public function path(): string
    {
        return 'db-query-logger';
    }
}
