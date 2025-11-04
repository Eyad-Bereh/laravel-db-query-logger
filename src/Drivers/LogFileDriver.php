<?php

namespace EyadBereh\LaravelDbQueryLogger\Drivers;

use EyadBereh\LaravelDbQueryLogger\Interfaces\FileNameGeneratorInterface;
use EyadBereh\LaravelDbQueryLogger\Interfaces\MessageFormatterInterface;
use EyadBereh\LaravelDbQueryLogger\Interfaces\PathGeneratorInterface;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class LogFileDriver extends AbstractDriver
{
    public function __construct(
        private readonly FileNameGeneratorInterface $fileNameGenerator,
        private readonly MessageFormatterInterface $messageFormatter,
        private readonly PathGeneratorInterface $pathGenerator
    )
    {
    }

    public function writeLog(): void
    {
        $content = $this->getCompiledMessage();
        $use_laravel_logs = config('db-query-logger.drivers.log_file.use_laravel_logs');

        if ($use_laravel_logs) {
            Log::debug($content);
        } else {
            $file_name = $this->fileNameGenerator->filename();
            $path = $this->pathGenerator->path();
            $filename = "$file_name.log";
            $fullpath = "$path/$filename";
            $disk = config('db-query-logger.drivers.log_file.disk'); // Get the disk from config

            Storage::disk($disk)->append($fullpath, $content);
        }
    }

    private function getCompiledMessage(): string
    {
        $format = $this->messageFormatter->format(); // get log message format

        // prepare placeholders data for compilation
        $data = [
            ':datetime:' => now()->format('Y-m-d H:i:s'),
            ':query:' => $this->query,
            ':bindings:' => json_encode($this->bindings),
            ':time:' => $this->time,
            ':connection:' => $this->connection,
            ':sql:' => $this->sql
        ];

        return strtr($format, $data); // compile and return the formatted message
    }
}
