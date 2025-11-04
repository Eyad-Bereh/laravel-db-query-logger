<?php

use EyadBereh\LaravelDbQueryLogger\Drivers\JsonFileDriver;
use EyadBereh\LaravelDbQueryLogger\Drivers\LogFileDriver;
use EyadBereh\LaravelDbQueryLogger\FileNameGenerators\DateFileNameGenerator;
use EyadBereh\LaravelDbQueryLogger\MessageFormatters\JsonMessageFormatter;
use EyadBereh\LaravelDbQueryLogger\MessageFormatters\LogMessageFormatter;
use EyadBereh\LaravelDbQueryLogger\PathGenerators\DefaultPathGenerator;

return [
    'enabled' => env('LARAVEL_DB_QUERY_LOGGER_ENABLED', true),

    'driver' => env('LARAVEL_DB_QUERY_LOGGER_DRIVER', 'log_file'),

    'drivers' => [
        'log_file' => [
            'concrete' => LogFileDriver::class,
            'file_name' => DateFileNameGenerator::class,
            'path' => DefaultPathGenerator::class,
            'message_formatter' => LogMessageFormatter::class,
            'use_laravel_logs' => false,
            'disk' => config('filesystems.default'),
        ],
        'json_file' => [
            'concrete' => JsonFileDriver::class,
            'file_name' => DateFileNameGenerator::class,
            'path' => DefaultPathGenerator::class,
            'message_formatter' => JsonMessageFormatter::class,
            'disk' => config('filesystems.default'),
        ],
    ],
];
