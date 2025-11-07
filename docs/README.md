---
description: >-
  This page introduces the package and clarifies its purpose, features, and
  limitations.
---

# Introduction

### Overview

The Laravel DB Query Logger is a robust package that enhances application observability by intercepting and logging all database interactions. It leverages Laravel's built-in database event system, specifically the `QueryExecuted` event to capture each query as it is executed. For every query, it logs a comprehensive entry that typically includes the complete SQL statement (with placeholders replaced by actual values), the precise execution time, the database connection used, and a timestamp. This output is essential for in-depth debugging, performance benchmarking, and auditing database activity. **Crucially, the package's role is passive logging; it will not highlight N+1 queries, redundant calls, or other common ORM-related inefficiencies. Its purpose is to provide a complete, un-opinionated record of all queries, leaving the analysis and optimization to the developer.**

### Core Features & Capabilities

* **Runtime Toggle:** The logger can be easily enabled or disabled via the `LARAVEL_DB_QUERY_LOGGER_ENABLED` environment variable, allowing you to turn it on for debugging in specific environments (like staging) and off in production.
* **Multiple Logging Drivers:** Supports different drivers for outputting the query logs, with `log_file` and `json_file` available out-of-the-box. This allows you to choose the format that best suits your analysis tools.
* **Structured JSON Logging:** The `json_file` driver logs each query as a structured JSON object, making it easy to parse, search, and analyze the logs using log management systems (like ELK Stack, Datadog) or custom scripts.
* **Traditional Text Logging:** The `log_file` driver provides a human-readable, plain text format for quick and easy tailing of log files during development.
* **Customizable Log Path & Filename:** The location and naming of the log files are highly configurable, using dedicated `PathGenerators` and `FileNameGenerators`, allowing for organized log storage (e.g., in a `logs/queries` directory).
* **Automatic Daily Log Files:** The default `DateFileNameGenerator` automatically creates separate log files for each day (e.g., `queries-2024-01-15.log`), which helps in managing log file size and searching logs from a specific date.
* **Flexible Message Formatting:** Each driver uses a dedicated `MessageFormatter` (`LogMessageFormatter` for text, `JsonMessageFormatter` for JSON), giving fine-grained control over how the query information is presented in the log entry.
* **Filesystem Disk Integration:** Leverages Laravel's Filesystem for log storage, meaning you can configure the logger to write to any configured disk (e.g., `local`, `s3`), enabling cloud storage of query logs.
* **Laravel Log Integration Option:** For the `log_file` driver, you have the option to bypass the package's file handling and send query logs directly to Laravel's standard log channel by setting `'use_laravel_logs' => true`.
* **Extensible Architecture:** The configuration reveals a driver-based, open-for-extension architecture. You can likely create custom Drivers, PathGenerators, FileNameGenerators, and MessageFormatters to tailor the logging behavior to your specific needs.

### Known Limitations & Bugs

Please be aware of the following constraints in the current version of the package:

* **Limited Database Testing:** The primary development and testing environment for this package is MySQL. Although it is built on Laravel's database layer and should function with other relational databases like PostgreSQL and SQLServer, they have not been formally verified. Use with these systems may require additional testing.
* **Absence of Query Filtering:** A known limitation is the lack of configurable filters. Consequently, the application will log every database query, which can lead to verbose logs in active environments. The ability to whitelist or blacklist queries based on tables, execution time, or other metrics is a planned enhancement for a subsequent update.
