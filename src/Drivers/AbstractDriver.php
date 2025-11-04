<?php

namespace EyadBereh\LaravelDbQueryLogger\Drivers;

use Illuminate\Support\Facades\DB;

abstract class AbstractDriver
{
//    protected QueryExecuted $event;

    protected readonly string $query;
    protected readonly array $bindings;
    protected readonly float $time;
    protected readonly string $connection;
    protected readonly string $sql;

    abstract protected function writeLog(): void;

    public function store()
    {
        $can_log = $this->canLogQueries();

        if ($can_log) {
            $this->writeLog();
        }
    }

    final public function setParameters(string $query, array $bindings, float $time, string $connection): void
    {
        $this->query = $query;
        $this->bindings = $bindings;
        $this->time = $time;
        $this->connection = $connection;
        $this->sql = DB::getQueryGrammar()->substituteBindingsIntoRawSql($query, $bindings);
    }

    private function canLogQueries(): bool
    {
        $conditions = [
            'is_enabled' => config('db-query-logger.enabled'),
        ];

        foreach ($conditions as $name => $is_satisfied) {
            if (!$is_satisfied) {
                return false;
            }
        }

        return true;
    }
}
