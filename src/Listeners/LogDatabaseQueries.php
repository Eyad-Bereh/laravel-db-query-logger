<?php

namespace EyadBereh\LaravelDbQueryLogger\Listeners;

use EyadBereh\LaravelDbQueryLogger\Drivers\AbstractDriver;
use Illuminate\Database\Events\QueryExecuted;

class LogDatabaseQueries
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(QueryExecuted $event): void
    {
        $queue_connection = config('queue.default');
        $driver = app(AbstractDriver::class);
        $driver->setParameters($event->sql, $event->bindings, $event->time, $event->connectionName);
        dispatch(function () use ($driver) {
            $driver->store();
        });
    }
}
