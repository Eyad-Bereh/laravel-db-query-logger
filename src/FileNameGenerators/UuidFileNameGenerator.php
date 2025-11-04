<?php

namespace EyadBereh\LaravelDbQueryLogger\FileNameGenerators;

use EyadBereh\LaravelDbQueryLogger\Interfaces\FileNameGeneratorInterface;
use Illuminate\Support\Str;

class UuidFileNameGenerator implements FileNameGeneratorInterface
{

    public function filename(): string
    {
        return Str::uuid();
    }
}