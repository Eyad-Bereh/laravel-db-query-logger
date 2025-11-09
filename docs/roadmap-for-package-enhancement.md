# Roadmap for Package Enhancement

I'm planning significant upgrades to increase the package's utility in production environments. The core initiatives are:

* **1. Comprehensive Filtering System** The current logger captures all queries. A future release will include a granular filtering system, giving developers fine-grained control over which queries are logged. Filters will be configurable based on:
  * **Connection & Database:** Target specific database connections.
  * **Tables:** Include or exclude queries affecting specific tables.
  * **Query Type:** Filter by operation (e.g., `SELECT`, `UPDATE`, `INSERT`).
  * **Performance:** Log only queries that exceed a defined execution time threshold.
* **2. High-Performance Database Driver** A new `database` driver will be added to persist logs to an RDBMS, facilitating complex analysis and integration with other application data. Acknowledging the potential performance overhead, this driver will be designed with a batching mechanism. Logs will be collected in a temporary buffer (e.g., in memory or a fast cache) and written to the database in bulk, either when a specific batch size is reached (e.g., 1000 queries) or after a time interval, thus minimizing I/O operations.
