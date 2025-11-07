# Message Formatter System

### Overview

The Message Formatter system provides a flexible, extensible mechanism for defining how query log entries are formatted in the Laravel DB Query Logger package. It uses a strategy pattern implementation that supports both plain text and structured data formats while maintaining a consistent interface.

### Architecture Overview

#### Core Interface

The system is built around the `MessageFormatterInterface`:

```php
interface MessageFormatterInterface
{
    public function format(): string|array;
}
```

This interface supports two return types:

* **string**: For plain text formatting with placeholders (used with LogFileDriver)
* **array**: For structured data formatting (used with JsonFileDriver)

### Available Formatters

#### 1. LogMessageFormatter

**Format Type:** Plain text string with placeholders

**Use Case:** Human-readable log files

* Creates easily readable log entries for developers
* Uses placeholder substitution for flexible formatting
* Ideal for tailing logs and quick debugging

**Implementation:**

```php
public function format(): string|array
{
    return '[:datetime:] - [query = :query:] - [bindings = :bindings:] - [time = :time: ms] - [connection = :connection:] - [sql = :sql:]';
}
```

**Example Output:**

```
[2023-10-27 14:30:25] - [query = select * from users where email = ?] - [bindings = ["test@example.com"]] - [time = 2.45 ms] - [connection = mysql] - [sql = select * from users where email = 'test@example.com']
```

#### 2. JsonMessageFormatter

**Format Type:** Structured array with placeholder mapping

**Use Case:** Machine-readable JSON output

* Defines the schema for JSON log entries
* Maps placeholder keys to JSON object properties
* Enables structured logging for log analysis systems

**Implementation:**

```php
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
```

**Example Output:**

```json
{
    "datetime": "2023-10-27 14:30:25",
    "query": "select * from users where email = ?",
    "bindings": ["test@example.com"],
    "time": 2.45,
    "connection": "mysql",
    "sql": "select * from users where email = 'test@example.com'"
}
```

### Configuration & Binding

#### Service Provider Registration

The message formatter is configured in the `LaravelDbQueryLoggerServiceProvider` through the `configureMessageFormatter()` method:

```php
private function configureMessageFormatter(): void
{
    $driver = config('db-query-logger.driver');
    $driver_info = config("db-query-logger.drivers.$driver");

    if (isset($driver_info['message_formatter'])) {
        $message_formatter = $driver_info['message_formatter'];
        
        // VALIDATION REQUIREMENTS:
        $reflection_class = new \ReflectionClass($message_formatter);
        
        // 1. Must not be abstract
        if ($reflection_class->isAbstract()) {
            throw new \Exception("The specified message formatter [$message_formatter] must not be abstract");
        }
        
        // 2. Must be instantiable
        if (! $reflection_class->isInstantiable()) {
            throw new \Exception("The specified message formatter [$message_formatter] must be instantiatable");
        }
        
        // 3. Must implement MessageFormatterInterface
        if (! $reflection_class->implementsInterface(MessageFormatterInterface::class)) {
            throw new \Exception("The specified message formatter [$message_formatter] must implement the interface [".MessageFormatterInterface::class.']');
        }
        
        // 4. Anonymous classes are NOT allowed (unlike other generators, this check is missing but implied)

        // Bind to Laravel container
        $this->app->bind(MessageFormatterInterface::class, $message_formatter);
    }
}
```

### Requirements for Custom Formatters

#### Mandatory Requirements

Based on the service provider validation, any custom message formatter must:

1. **Be a Concrete Class** - Cannot be declared as `abstract`
2. **Be Instantiable** - Must be able to be constructed by Laravel's container
3. **Implement the Interface** - Must implement `MessageFormatterInterface`
4. **Return Valid Types** - Must return either `string` or `array` from the `format()` method

#### Driver-Specific Return Type Requirements

**For LogFileDriver (Text Logging):**

* **Required Return Type:** `string`
* **Format:** Must use colon-wrapped placeholders (`:placeholder:`)
* **Purpose:** Used with `strtr()` function for simple text substitution
*   **Example Valid Return:**

    ```php
    public function format(): string
    {
        return 'Query: :sql: | Time: :time:ms';
    }
    ```

**For JsonFileDriver (JSON Logging):**

* **Required Return Type:** `array`
* **Format:** Must define a schema mapping placeholders to JSON properties
* **Purpose:** Used with recursive `compileJsonSchema()` method
*   **Example Valid Return:**

    ```php
    public function format(): array
    {
        return [
            'timestamp' => ':datetime:',
            'query_info' => [
                'raw' => ':query:',
                'executed' => ':sql:'
            ]
        ];
    }
    ```

#### Invalid Formatter Examples

**Abstract Class (Violates Requirement #1)**

```php
abstract class AbstractFormatter implements MessageFormatterInterface
{
    // INVALID: Cannot be abstract
}
```

**Non-Instantiable Class (Violates Requirement #2)**

```php
class NonInstantiableFormatter implements MessageFormatterInterface
{
    private function __construct() {} // INVALID: Private constructor
    
    public function format(): string|array
    {
        return 'test';
    }
}
```

**Wrong Interface (Violates Requirement #3)**

```php
class WrongInterfaceFormatter // INVALID: Doesn't implement MessageFormatterInterface
{
    public function format(): string
    {
        return 'test';
    }
}
```

**Invalid Return Type (Violates Requirement #4)**

```php
class InvalidReturnFormatter implements MessageFormatterInterface
{
    public function format(): \stdClass // INVALID: Must return string|array
    {
        return new \stdClass();
    }
}
```

### Configuration Examples

#### Default Configuration

```php
'drivers' => [
    'log_file' => [
        'concrete' => LogFileDriver::class,
        'file_name' => DateFileNameGenerator::class,
        'path' => DefaultPathGenerator::class,
        'message_formatter' => LogMessageFormatter::class, // ← Returns string
        'use_laravel_logs' => false,
        'disk' => config('filesystems.default'),
    ],
    'json_file' => [
        'concrete' => JsonFileDriver::class,
        'file_name' => DateFileNameGenerator::class,
        'path' => DefaultPathGenerator::class,
        'message_formatter' => JsonMessageFormatter::class, // ← Returns array
        'disk' => config('filesystems.default'),
    ],
],
```

### Creating Custom Formatters

#### Custom Text Formatter (for LogFileDriver)

```php
<?php

namespace App\CustomFormatters;

use EyadBereh\LaravelDbQueryLogger\Interfaces\MessageFormatterInterface;

class CustomTextFormatter implements MessageFormatterInterface
{
    public function format(): string
    {
        return '🚀 [:datetime:] | ⏱️ :time:ms | 🔗 :connection: | 💬 :sql:';
    }
}
```

#### Custom JSON Formatter (for JsonFileDriver)

```php
<?php

namespace App\CustomFormatters;

use EyadBereh\LaravelDbQueryLogger\Interfaces\MessageFormatterInterface;

class CustomJsonFormatter implements MessageFormatterInterface
{
    public function format(): array
    {
        return [
            'metadata' => [
                'timestamp' => ':datetime:',
                'execution_time_ms' => ':time:',
                'db_connection' => ':connection:',
            ],
            'query_details' => [
                'raw_sql' => ':query:',
                'compiled_sql' => ':sql:',
                'parameters' => ':bindings:',
            ],
            'performance' => [
                'duration' => ':time:',
                'is_slow' => ':time:' > 100, // This won't work with placeholders
            ]
        ];
    }
}
```

### Usage in Drivers

#### LogFileDriver Usage:

```php
private function getCompiledMessage(): string
{
    $format = $this->messageFormatter->format(); // Must return string
    // ... placeholder substitution logic
    return strtr($format, $data);
}
```

#### JsonFileDriver Usage:

```php
private function getJsonObject()
{
    $message_formatter = $this->messageFormatter->format(); // Must return array
    $object = $this->compileJsonSchema($message_formatter, $data);
    return $object;
}
```

### Best Practices for Custom Formatters

#### For Text Formatters:

* Keep lines reasonably short for readability
* Use consistent formatting patterns
* Include essential information (timestamp, SQL, time)
* Consider log file parsing tools

#### For JSON Formatters:

* Use descriptive property names
* Group related data in nested objects
* Consider your log analysis tool's requirements
* Maintain backward compatibility when updating schemas

