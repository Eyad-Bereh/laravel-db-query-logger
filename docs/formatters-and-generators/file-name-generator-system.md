# File Name Generator System

### Overview

The File Name Generator system provides a flexible, extensible mechanism for generating log file names in the Laravel DB Query Logger package. It uses a strategy pattern implementation that allows for multiple naming strategies while maintaining a consistent interface.

### Architecture Overview

#### Core Interface

The system is built around the `FileNameGeneratorInterface`:

```php
interface FileNameGeneratorInterface
{
    public function filename(): string;
}
```

This simple interface ensures that all file name generators implement a single `filename()` method that returns a string, making the system highly extensible and consistent.

### Available Generators

#### 1. DateFileNameGenerator

**Pattern:** `Y-m-d` (e.g., `2023-10-27.log`)

**Use Case:** Daily log rotation

* Creates one log file per day
* Ideal for production environments with moderate query volume
* Makes it easy to locate logs from specific dates
* Prevents individual files from becoming too large

**Implementation:**

```php
public function filename(): string
{
    return now()->format('Y-m-d');
}
```

#### 2. DatetimeFileNameGenerator

**Pattern:** `Y-m-d H:i:s` (e.g., `2023-10-27 14:30:25.log`)

**Use Case:** High-precision logging and debugging

* Creates unique files with timestamp precision down to seconds
* Useful for debugging specific time periods or requests
* Can create many small files - use with caution in high-traffic environments

**Implementation:**

```php
public function filename(): string
{
    return now()->format('Y-m-d H:i:s');
}
```

#### 3. TimestampFileNameGenerator

**Pattern:** Unix timestamp (e.g., `1698413425.log`)

**Use Case:** Programmatic log processing

* Machine-readable file names
* Useful for sorting and processing logs programmatically
* Avoids any date format ambiguities

**Implementation:**

```php
public function filename(): string
{
    return now()->format('U');
}
```

#### 4. UuidFileNameGenerator

**Pattern:** UUID (e.g., `f47ac10b-58cc-4372-a567-0e02b2c3d479.log`)

**Use Case:** Distributed systems and unique file identification

* Guarantees unique file names across multiple servers
* Ideal for distributed applications running on multiple instances
* Prevents file naming conflicts in cloud environments

**Implementation:**

```php
public function filename(): string
{
    return Str::uuid();
}
```

### Configuration & Binding

#### Service Provider Registration

The file name generator is configured in the `LaravelDbQueryLoggerServiceProvider` through the `configureFileNameGenerator()` method:

```php
private function configureFileNameGenerator(): void
{
    $driver = config('db-query-logger.driver');
    $driver_info = config("db-query-logger.drivers.$driver");

    if (isset($driver_info['file_name'])) {
        $file_name_generator = $driver_info['file_name'];
        
        // Validation checks
        $reflection_class = new \ReflectionClass($file_name_generator);
        
        if ($reflection_class->isAbstract()) {
            throw new \Exception("The specified file name generator must not be abstract");
        }
        
        if (!$reflection_class->isInstantiable()) {
            throw new \Exception("The specified file name generator must be instantiatable");
        }
        
        if (!$reflection_class->implementsInterface(FileNameGeneratorInterface::class)) {
            throw new \Exception("The specified file name generator must implement FileNameGeneratorInterface");
        }
        
        if ($reflection_class->isAnonymous()) {
            throw new \Exception("The specified file name generator must not be anonymous");
        }

        // Bind to Laravel container
        $this->app->bind(FileNameGeneratorInterface::class, $file_name_generator);
    }
}
```

#### Configuration Example

In `config/db-query-logger.php`:

```php
'drivers' => [
    'log_file' => [
        'concrete' => LogFileDriver::class,
        'file_name' => DateFileNameGenerator::class, // ← Generator specified here
        'path' => DefaultPathGenerator::class,
        'message_formatter' => LogMessageFormatter::class,
        'use_laravel_logs' => false,
        'disk' => config('filesystems.default'),
    ],
],
```

### How It Works

#### 1. Dependency Injection

* The configured generator class is bound to `FileNameGeneratorInterface` in Laravel's service container
* When a driver is instantiated, the generator is automatically injected via constructor injection

#### 2. File Name Generation Flow

```php
// In LogFileDriver or JsonFileDriver
$file_name = $this->fileNameGenerator->filename();
$filename = "$file_name.log"; // or "$file_name.json" for JSON driver
```

#### 3. Validation Safeguards

The service provider includes comprehensive validation:

* **Non-abstract**: Must be a concrete, instantiable class
* **Implements Interface**: Must satisfy the `FileNameGeneratorInterface` contract
* **Non-anonymous**: Cannot be an anonymous class (because this prevents Laravel from serializing and caching the application configuration)
* **Instantiable**: Must be able to be constructed by Laravel's container

### Usage Examples

#### Default Daily Rotation

```php
// Generates: queries-2023-10-27.log
'drivers' => [
    'log_file' => [
        'file_name' => DateFileNameGenerator::class,
        // ... other config
    ],
],
```

#### High-Precision Debugging

```php
// Generates: queries-2023-10-27 14:30:25.log  
'drivers' => [
    'log_file' => [
        'file_name' => DatetimeFileNameGenerator::class,
        // ... other config
    ],
],
```

#### Distributed System Setup

```php
// Generates: queries-f47ac10b-58cc-4372-a567-0e02b2c3d479.log
'drivers' => [
    'log_file' => [
        'file_name' => UuidFileNameGenerator::class,
        // ... other config
    ],
],
```

### Best Practices

#### Choose Based on Use Case

* **Production**: `DateFileNameGenerator` for daily rotation
* **Development**: `DatetimeFileNameGenerator` for precise debugging
* **Distributed Systems**: `UuidFileNameGenerator` to avoid conflicts
* **API/Processing**: `TimestampFileNameGenerator` for programmatic handling

#### File Management Considerations

* Date-based generators create predictable, manageable file counts
* UUID/timestamp generators can create unlimited files - implement cleanup policies
* Consider your log retention and archiving strategy when choosing a generator

### Extending the System

#### Creating Custom Generators

```php
<?php

namespace App\CustomGenerators;

use EyadBereh\LaravelDbQueryLogger\Interfaces\FileNameGeneratorInterface;

class ApplicationNameGenerator implements FileNameGeneratorInterface
{
    public function filename(): string
    {
        return config('app.name') . '-' . now()->format('Y-m-d');
    }
}
```

#### Using Custom Generators

```php
'drivers' => [
    'log_file' => [
        'file_name' => \App\CustomGenerators\ApplicationNameGenerator::class,
        // ... other config
    ],
],
```

The file name generator system provides a robust, flexible foundation for log file management while maintaining simplicity through its single-responsibility interface design.
