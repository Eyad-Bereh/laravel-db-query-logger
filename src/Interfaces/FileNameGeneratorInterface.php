<?php

namespace EyadBereh\LaravelDbQueryLogger\Interfaces;

interface FileNameGeneratorInterface {
    public function filename(): string;
}