<?php

namespace EyadBereh\LaravelDbQueryLogger\Facades;

use Illuminate\Support\Facades\Facade;

class LaravelDbQueryLogger extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-db-query-logger';
    }
}
