<?php

namespace EyadBereh\LaravelDbQueryLogger\MessageFormatters;

use EyadBereh\LaravelDbQueryLogger\Interfaces\MessageFormatterInterface;

class JsonMessageFormatter implements MessageFormatterInterface
{
    public function format(): string|array
    {
        return [
            'datetime' => ':datetime:',
            'query' => ':query:',
            'bindings' => ':bindings:',
            'time' => ':time:',
            'connection' => ':connection:',
            'sql' => ':sql:',
        ];
    }
}
