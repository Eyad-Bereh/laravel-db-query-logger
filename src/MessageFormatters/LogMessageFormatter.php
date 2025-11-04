<?php

namespace EyadBereh\LaravelDbQueryLogger\MessageFormatters;

use EyadBereh\LaravelDbQueryLogger\Interfaces\MessageFormatterInterface;

class LogMessageFormatter implements MessageFormatterInterface
{

    public function format(): string|array
    {
        return '[:datetime:] - [query = :query:] - [bindings = :bindings:] - [time = :time: ms] - [connection = :connection:] - [sql = :sql:]';
    }
}