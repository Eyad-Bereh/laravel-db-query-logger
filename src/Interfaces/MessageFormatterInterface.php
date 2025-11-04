<?php

namespace EyadBereh\LaravelDbQueryLogger\Interfaces;
interface MessageFormatterInterface {
    public function format(): string|array;
}