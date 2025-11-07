# Path Generator System

### Overview

The Path Generator system provides a flexible, extensible mechanism for determining where log files are stored in the Laravel DB Query Logger package. It uses a strategy pattern implementation that allows for multiple storage location strategies while maintaining a consistent interface.

### Architecture Overview

#### Core Interface

The system is built around the `PathGeneratorInterface`:

```php
interface PathGeneratorInterface
{
    public function path(): string;
}
```

This simple interface ensures that all path generators implement a single `path()` method that returns a string representing the storage directory, making the system highly extensible and consistent.

### Available Generators

Based on the code structure, here are the available path generators:

#### 1. DefaultPathGenerator

**Path:** `'db-query-logger'`

**Use Case:** Standardized log storage

* Creates a dedicated directory for query logs
* Keeps logs organized and separate from other application files
* Works with any filesystem disk (local, S3, etc.)

**Implementation:**

```php
public function path(): string
{
    return 'db-query-logger';
}
```

**Resulting Path Examples:**

* **Local disk:** `storage/app/db-query-logger/`
* **S3 disk:** `db-query-logger/` (in the S3 bucket root)

### Configuration & Binding

#### Service Provider Registration

The path generator is configured in the `LaravelDbQueryLoggerServiceProvider` through the `configurePathGenerator()` method:

```php
private function configurePathGenerator(): void
{
    $driver = config('db-query-logger.driver');
    $driver_info = config("db-query-logger.drivers.$driver");

    if (isset($driver_info['path'])) {
        $path_generator = $driver_info['path'];
        
        // Validation checks
        $reflection_class = new \ReflectionClass($path_generator);
        
        if ($reflection_class->isAbstract()) {
            throw new \Exception("The specified path generator must not be abstract");
        }
        
        if (!$reflection_class->isInstantiable()) {
            throw new \Exception("The specified path generator must be instantiatable");
        }
        
        if (!$reflection_class->implementsInterface(PathGeneratorInterface::class)) {
            throw new \Exception("The specified path generator must implement PathGeneratorInterface");
        }
        
        if ($reflection_class->isAnonymous()) {
            throw new \Exception("The specified path generator must not be anonymous");
        }

        // Bind to Laravel container
        $this->app->bind(PathGeneratorInterface::class, $path_generator);
    }
}
```

#### Configuration Example

In `config/db-query-logger.php`:

```php
'drivers' => [
    'log_file' => [
        'concrete' => LogFileDriver::class,
        'file_name' => DateFileNameGenerator::class,
        'path' => DefaultPathGenerator::class, // ← Path generator specified here
        'message_formatter' => LogMessageFormatter::class,
        'use_laravel_logs' => false,
        'disk' => config('filesystems.default'),
    ],
],
```

### How It Works

#### 1. Dependency Injection

* The configured generator class is bound to `PathGeneratorInterface` in Laravel's service container
* When a driver is instantiated, the path generator is automatically injected via constructor injection

#### 2. Path Generation Flow

```php
// In LogFileDriver or JsonFileDriver
$path = $this->pathGenerator->path();
$file_name = $this->fileNameGenerator->filename();
$fullpath = "$path/$filename"; // Combines path and file name
```

#### 3. Complete Path Construction Example

```php
// With DefaultPathGenerator and DateFileNameGenerator
$path = 'db-query-logger'; // From PathGenerator
$file_name = '2023-10-27'; // From FileNameGenerator  
$filename = '2023-10-27.log'; // With extension added by driver
$fullpath = 'db-query-logger/2023-10-27.log'; // Final storage path
```

#### 4. Filesystem Integration

```php
// Storage operation using the generated path
$disk = config('db-query-logger.drivers.log_file.disk');
Storage::disk($disk)->append($fullpath, $content);
```

### Storage Disk Integration

The path generator works in conjunction with Laravel's filesystem disks:

#### Local Disk Example

```php
'disk' => 'local', // Uses storage/app/ as base path
// Full path: storage/app/db-query-logger/2023-10-27.log
```

#### S3 Disk Example

```php
'disk' => 's3', // Uses S3 bucket as base path
// Full path: https://your-bucket.s3.region.amazonaws.com/db-query-logger/2023-10-27.log
```

#### Custom Disk Example

```php
'disk' => 'logs', // Custom disk defined in config/filesystems.php
// Full path: Whatever base path is configured for the 'logs' disk
```

### Validation Safeguards

The service provider includes comprehensive validation:

* **Non-abstract**: Must be a concrete, instantiable class
* **Implements Interface**: Must satisfy the `PathGeneratorInterface` contract
* **Non-anonymous**: Cannot be an anonymous class (because this prevents Laravel from serializing and caching the application configuration)
* **Instantiable**: Must be able to be constructed by Laravel's container

### Extending the System

#### Creating Custom Path Generators

**Environment-Based Path Generator**

```php
<?php

namespace App\CustomGenerators;

use EyadBereh\LaravelDbQueryLogger\Interfaces\PathGeneratorInterface;

class EnvironmentPathGenerator implements PathGeneratorInterface
{
    public function path(): string
    {
        $env = app()->environment();
        return "db-queries/{$env}";
    }
}
```

**Result:** `db-queries/production/` or `db-queries/staging/`

**Application-Based Path Generator**

```php
<?php

namespace App\CustomGenerators;

use EyadBereh\LaravelDbQueryLogger\Interfaces\PathGeneratorInterface;

class ApplicationPathGenerator implements PathGeneratorInterface
{
    public function path(): string
    {
        $appName = config('app.name', 'laravel');
        $sanitized = preg_replace('/[^a-zA-Z0-9_-]/', '-', $appName);
        return "logs/{$sanitized}/db-queries";
    }
}
```

**Result:** `logs/my-app/db-queries/`

**Date-Structured Path Generator**

```php
<?php

namespace App\CustomGenerators;

use EyadBereh\LaravelDbQueryLogger\Interfaces\PathGeneratorInterface;

class DateStructuredPathGenerator implements PathGeneratorInterface
{
    public function path(): string
    {
        return 'db-queries/' . now()->format('Y/m/d');
    }
}
```

**Result:** `db-queries/2023/10/27/`

#### Using Custom Generators

```php
'drivers' => [
    'log_file' => [
        'path' => \App\CustomGenerators\EnvironmentPathGenerator::class,
        // ... other config
    ],
],
```

### Best Practices

#### Path Naming Conventions:

* Use lowercase with hyphens for consistency
* Avoid spaces and special characters
* Consider including environment or application context
* Keep paths reasonably short for readability

#### Storage Considerations:

* **Local development**: Default path is usually sufficient
* **Production**: Consider environment-specific paths for multi-tenant apps
* **Cloud storage**: Use structured paths for better organization in S3/GCS
* **Security**: Avoid exposing sensitive information in path names

#### Multi-Server Environments:

```php
// For distributed systems, include server identifier
class ServerAwarePathGenerator implements PathGeneratorInterface
{
    public function path(): string
    {
        $serverId = gethostname(); // or from environment variable
        return "db-queries/{$serverId}";
    }
}
```

### Complete File Path Examples

| Generator                  | File Name    | Disk    | Final Path                                                              |
| -------------------------- | ------------ | ------- | ----------------------------------------------------------------------- |
| `DefaultPathGenerator`     | `2023-10-27` | `local` | `storage/app/db-query-logger/2023-10-27.log`                            |
| `DefaultPathGenerator`     | `2023-10-27` | `s3`    | `https://bucket.s3.region.amazonaws.com/db-query-logger/2023-10-27.log` |
| `EnvironmentPathGenerator` | `1698413425` | `local` | `storage/app/db-queries/production/1698413425.log`                      |

The path generator system provides a clean separation of concerns, allowing you to define where logs are stored independently of how they're named or formatted, while maintaining full integration with Laravel's filesystem abstraction.
