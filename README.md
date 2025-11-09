# Laravel Database Query Logger

[![Latest Version on Packagist](https://img.shields.io/packagist/v/eyad-mohammed-osama/laravel-db-query-logger.svg?style=flat-square)](https://packagist.org/packages/eyad-mohammed-osama/laravel-db-query-logger)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/eyad-mohammed-osama/laravel-db-query-logger/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/eyad-mohammed-osama/laravel-db-query-logger/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/eyad-mohammed-osama/laravel-db-query-logger/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/eyad-mohammed-osama/laravel-db-query-logger/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/eyad-mohammed-osama/laravel-db-query-logger.svg?style=flat-square)](https://packagist.org/packages/eyad-mohammed-osama/laravel-db-query-logger)

The Laravel DB Query Logger is a robust package that enhances application observability by intercepting and logging all
database interactions.

## Installation

You can install the package via composer:

```bash
composer require eyad-bereh/laravel-db-query-logger
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-db-query-logger-config"
```

This is the contents of the published config file:

```php
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
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [Eyad Bereh](https://github.com/Eyad-Mohammed-Osama)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
