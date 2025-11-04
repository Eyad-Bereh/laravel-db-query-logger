<?php

namespace EyadBereh\LaravelDbQueryLogger\FileNameGenerators;

use EyadBereh\LaravelDbQueryLogger\Interfaces\FileNameGeneratorInterface;

class DateFileNameGenerator implements FileNameGeneratorInterface
{
    public function filename(): string
    {
        return now()->format('Y-m-d');
    }
}