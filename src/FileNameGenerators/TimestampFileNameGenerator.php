<?php

namespace EyadBereh\LaravelDbQueryLogger\FileNameGenerators;

use EyadBereh\LaravelDbQueryLogger\Interfaces\FileNameGeneratorInterface;

class TimestampFileNameGenerator implements FileNameGeneratorInterface
{
    public function filename(): string
    {
        return now()->format('U');
    }
}