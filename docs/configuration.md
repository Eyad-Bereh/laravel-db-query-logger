---
description: This page explains the configuration of the package.
---

# Configuration

### **Publishing the Config File**

To customize the package's behavior, you must first publish its configuration file.

1. Run the following Artisan command:

```bash
php artisan vendor:publish --provider=EyadBereh\\LaravelDbQueryLogger\\LaravelDbQueryLoggerServiceProvider
```

2. This will create a new configuration file at `config/db-query-logger.php`, where you can define your logging drivers, file paths, and formatting options.

### **Upgrading the Package**

When upgrading to a new version of the package, the configuration file may have been updated.

1. **Back up your existing configuration:** Copy any custom values from your current `config/db-query-logger.php` file.
2. **Force-publish the new configuration:** Run the publish command with the `--force` flag to overwrite your old file with the latest version.

```bash
php artisan vendor:publish --provider="EyadBereh\LaravelDbQueryLogger\LaravelDbQueryLoggerServiceProvider" --force
```

3. **Restore your settings:** Transfer your backed-up custom values into the newly published configuration file.

{% hint style="danger" %}
To ensure you don't lose your custom configuration values, make sure to back up your `config/db-query-logger.php` file before using the `--force` flag to republish.
{% endhint %}

### Configuration File Contents

The following is a complete overview of the default `db-query-logger.php` configuration file:

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

The following section provides an exhaustive, line-by-line analysis of the `db-query-logger.php` configuration file. We will deconstruct each key, its purpose, available options, and how it fundamentally controls the package's behavior.

#### **1. Global Enable/Disable Switch**

```php
'enabled' => env('LARAVEL_DB_QUERY_LOGGER_ENABLED', true),
```

* **Purpose:** This is the master switch for the entire package.
* **Behavioral Impact:**
  * When set to `true`, the package actively listens to Laravel's database events and logs every query.
  * When set to `false`, the package is completely inert—it does not register its event listeners, resulting in zero performance overhead from logging.
* **Usage Recommendation:** It is highly advised to control this via the `.env` file. You should set it to `true` in your local and staging environments for debugging, and `false` in production to avoid unnecessary I/O operations and log file bloat.
  * Example `.env` entry: `LARAVEL_DB_QUERY_LOGGER_ENABLED=false`

#### **2. Primary Driver Selection**

```php
'driver' => env('LARAVEL_DB_QUERY_LOGGER_DRIVER', 'log_file'),
```

* **Purpose:** Determines which logging "driver" (or strategy) is used for output. This points to one of the drivers defined in the `drivers` array below.
* **Behavioral Impact:** This is the most fundamental choice affecting your log's format and storage.
  * `'log_file'`: Outputs human-readable, plain-text logs.
  * `'json_file'`: Outputs structured JSON logs, where each query is a separate JSON object, ideal for machine parsing.
* **Default Value:** If the environment variable is not set, it will fall back to `'log_file'`.

#### **3. Drivers Configuration Array**

```php
'drivers' => [ ... ],
```

* **Purpose:** This array contains the detailed configuration for every available logging driver. The package's architecture allows for easy extensibility by adding new custom drivers here.

{% hint style="info" %}
Don't worry, we'll discuss the available drivers in detail.
{% endhint %}

### What About The (Generators) and (Formatters) Classes?

The configuration file leverages a flexible architecture through dedicated generator and formatter classes.&#x20;

* The **generators**—`PathGenerator` and `FileNameGenerator`—are responsible for the log file's structure and location, allowing you to define where files are saved and how they are named (e.g., using the included `DateFileNameGenerator` for daily log rotation).&#x20;
* The **message formatters**, on the other hand, control the content and format of each individual log entry. They transform the raw query data into a specific output format, such as plain text via the `LogMessageFormatter` for human readability or structured JSON via the `JsonMessageFormatter` for machine consumption and integration with log analysis tools. This separation of concerns makes the logging output highly customizable.

{% hint style="info" %}
There's a dedicated section explaining generators and formatters.
{% endhint %}
