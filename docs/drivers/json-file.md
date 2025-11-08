# JSON File

### Overview

The `JsonFileDriver` is a concrete implementation that writes database queries as structured JSON objects to log files. This driver is specifically designed for machine readability and integration with log analysis systems, providing superior query analytics capabilities compared to plain text logging.

This driver specializes in:

* Serializing query data into structured JSON format
* Maintaining valid JSON files with proper array structures
* Supporting complex JSON schema formatting through message formatters
* Enabling seamless integration with log management and analysis tools

### Configuration

The driver is configured in `config/db-query-logger.php` under the `drivers.json_file` section:

```php
'json_file' => [
    'concrete' => JsonFileDriver::class,
    'file_name' => DateFileNameGenerator::class,
    'path' => DefaultPathGenerator::class,
    'message_formatter' => JsonMessageFormatter::class,
    'disk' => config('filesystems.default'),
],
```

### Key Features

#### Structured JSON Output

* Each query is stored as a complete JSON object within a JSON array
* Maintains proper data types (floats for time, arrays for bindings, etc.)
* Preserves the original query structure with separate fields for raw SQL and compiled SQL

#### Intelligent File Management

* **JSON Array Integrity**: Automatically manages the JSON array structure, properly appending new entries while maintaining valid JSON syntax
* **Pretty Printing**: Uses `JSON_PRETTY_PRINT` for human-readable JSON formatting while maintaining machine-parsability

#### Flexible JSON Schema

The driver supports customizable JSON structures through the message formatter interface, allowing you to:

* Rearrange field order
* Nest related data in sub-objects
* Include or exclude specific fields
* Transform field values

### Data Structure

#### Default JSON Output

```json
[
    {
        "datetime": "2023-10-27 14:30:25",
        "query": "select * from `users` where `email` = ?",
        "bindings": ["test@example.com"],
        "time": 2.45,
        "connection": "mysql",
        "sql": "select * from `users` where `email` = 'test@example.com'"
    }
]
```

#### Available Data Fields

| Field        | Type   | Description                                           |
| ------------ | ------ | ----------------------------------------------------- |
| `datetime`   | string | Query execution timestamp in `Y-m-d H:i:s` format     |
| `query`      | string | Raw SQL query with parameter placeholders             |
| `bindings`   | array  | Query parameters as JSON array                        |
| `time`       | float  | Execution time in milliseconds with decimal precision |
| `connection` | string | Database connection name                              |
| `sql`        | string | Fully compiled SQL with bindings substituted          |

### Advanced Schema Customization

The driver supports complex JSON structures through the `compileJsonSchema` method. For example, a custom formatter could create nested structures like:

```json
{
    "metadata": {
        "timestamp": "2023-10-27 14:30:25",
        "connection": "mysql",
        "duration_ms": 2.45
    },
    "query_data": {
        "raw": "select * from `users` where `email` = ?",
        "compiled": "select * from `users` where `email` = 'test@example.com'",
        "parameters": ["test@example.com"]
    }
}
```

### File Management

* **File Names**: Uses `FileNameGeneratorInterface` (default: daily dates with `.json` extension)
* **Storage**: Controlled by `PathGeneratorInterface` and configurable disk
* **Append Logic**: Intelligently parses existing JSON array and appends new objects
* **Error Resilient**: Handles corrupted JSON by resetting the file content

### Integration Benefits

#### Log Analysis Tools

* **ELK Stack (Elasticsearch, Logstash, Kibana)**: Direct ingestion and indexing of JSON fields
* **Grafana Loki**: Structured logging with label extraction from JSON fields
* **Datadog/Splunk**: Automatic field parsing and visualization

#### Analytics Capabilities

* **Performance Monitoring**: Create dashboards for average query time using the `time` field
* **Query Analysis**: Identify frequently executed queries by analyzing the `query` field
* **Connection Tracking**: Monitor database load distribution across connections
* **Binding Analysis**: Examine common parameter patterns through the `bindings` field

### Best Practices

1. **Use for Production Analytics**: Ideal for environments where query performance monitoring is critical
2. **Configure Proper Retention**: Implement log rotation policies for JSON files
3. **Leverage Cloud Storage**: Use S3 or similar disks for centralized log collection
4. **Monitor File Sizes**: JSON files can grow quickly with high query volumes
5. **Use with Log Shippers**: Integrate with Filebeat, Fluentd, or similar tools for real-time processing

### Performance Considerations

* **Memory Usage**: The entire JSON file is read into memory when appending new entries
* **File Locking**: Uses atomic file operations to prevent corruption during concurrent writes
* **Storage Overhead**: JSON formatting and pretty printing increase storage requirements compared to plain text

### Example Use Cases

#### Performance DashboardDebugging Complex Issues

```bash
# Extract all queries from a specific connection
jq '.[] | select(.connection == "mysql")' queries-2023-10-27.json

# Find queries with specific bindings
jq '.[] | select(.bindings | contains(["admin"]))' queries-2023-10-27.json
```

The `JsonFileDriver` provides enterprise-grade logging capabilities that transform simple query logging into a powerful analytics and monitoring solution.
