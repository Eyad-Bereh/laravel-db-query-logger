# Log File

### Overview

The `LogFileDriver` is a concrete implementation of the logging driver that writes database queries to plain text log files. It provides flexible output options, allowing you to either integrate with Laravel's default logging system or use dedicated log files.

This driver is responsible for:

* Formatting query data into human-readable text messages
* Writing logs to Laravel's default log channel or dedicated files
* Managing log file paths and names through configurable generators
* Supporting daily log file rotation by default

### Configuration

The driver is configured in `config/db-query-logger.php` under the `drivers.log_file` section:

```php
'log_file' => [
    'concrete' => LogFileDriver::class,
    'file_name' => DateFileNameGenerator::class,
    'path' => DefaultPathGenerator::class,
    'message_formatter' => LogMessageFormatter::class,
    'use_laravel_logs' => false,
    'disk' => config('filesystems.default'),
],
```

### Key Features

#### Dual Output Modes

**1. Laravel Log Integration (`use_laravel_logs = true`)**

* Writes queries to Laravel's default log channel (`storage/logs/laravel.log`)
* Uses Laravel's built-in log rotation and formatting
* Logs appear with the `debug` level
* **Best for**: Simple setups where you want all logs in one place

**2. Dedicated Log Files (`use_laravel_logs = false`) -&#x20;**_**Recommended**_

* Creates separate log files specifically for database queries
* Files are stored in a configurable directory (default: `storage/logs/db-queries/`)
* Uses daily file rotation (e.g., `queries-2023-10-27.log`)
* **Best for**: Production environments where you need clean, separated logs

#### Message Formatting

The driver uses a placeholder-based formatting system with the following available variables:

| Placeholder    | Description                          | Example                                                |
| -------------- | ------------------------------------ | ------------------------------------------------------ |
| `:datetime:`   | Current timestamp                    | `2023-10-27 14:30:25`                                  |
| `:query:`      | Raw SQL with placeholders            | `select * from users where email = ?`                  |
| `:bindings:`   | JSON-encoded query parameters        | `["test@example.com"]`                                 |
| `:time:`       | Query execution time in milliseconds | `2.45`                                                 |
| `:connection:` | Database connection name             | `mysql`                                                |
| `:sql:`        | Fully compiled SQL with bindings     | `select * from users where email = 'test@example.com'` |

#### File Management

* **File Names**: Controlled by `FileNameGeneratorInterface` (default: daily dates)
* **Storage Path**: Controlled by `PathGeneratorInterface`
* **Disk Support**: Can use any Laravel filesystem disk (local, S3, etc.)
* **Append Mode**: New entries are appended to existing files without overwriting

### Usage Examples

#### Sample Output with Default Formatting

When `use_laravel_logs = false`, a typical log entry looks like:

```
[2023-10-27 14:30:25] [query = select * from `users` where `users`.`deleted_at` is null and `email_verified_at` is not null limit 1] - [bindings = []] - [time = 3.71 ms] - [connection = mysql] - [sql = select * from `users` where `users`.`deleted_at` is null and `email_verified_at` is not null limit 1]
```

When `use_laravel_logs = true`, the same query appears in `laravel.log` as:

```
[2023-10-27 14:30:25] local.DEBUG: [2023-10-27 14:30:25] [query = select * from `users` where `users`.`deleted_at` is null and `email_verified_at` is not null limit 1] - [bindings = []] - [time = 3.71 ms] - [connection = mysql] - [sql = select * from `users` where `users`.`deleted_at` is null and `email_verified_at` is not null limit 1]
```

### Dependencies

The driver requires three interface implementations:

1. **`FileNameGeneratorInterface`** - Generates log file names
2. **`MessageFormatterInterface`** - Defines the log message format
3. **`PathGeneratorInterface`** - Determines where log files are stored

### Best Practices

1. **Use dedicated files in production** to avoid polluting your main application logs
2. **Configure log rotation** to prevent disk space issues
3. **Use a separate disk** (like S3) for log storage in multi-server environments
4. **Monitor log file sizes** when logging high-traffic applications
