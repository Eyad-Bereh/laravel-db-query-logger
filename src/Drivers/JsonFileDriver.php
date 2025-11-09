<?php

namespace EyadBereh\LaravelDbQueryLogger\Drivers;

use EyadBereh\LaravelDbQueryLogger\Interfaces\FileNameGeneratorInterface;
use EyadBereh\LaravelDbQueryLogger\Interfaces\MessageFormatterInterface;
use EyadBereh\LaravelDbQueryLogger\Interfaces\PathGeneratorInterface;
use Illuminate\Support\Facades\Storage;

class JsonFileDriver extends AbstractDriver
{
    public function __construct(
        private readonly FileNameGeneratorInterface $fileNameGenerator,
        private readonly MessageFormatterInterface $messageFormatter,
        private readonly PathGeneratorInterface $pathGenerator
    ) {}

    public function writeLog(): void
    {
        $file_name = $this->fileNameGenerator->filename();
        $path = $this->pathGenerator->path();
        $filename = "$file_name.json";
        $fullpath = "$path/$filename";
        $content = $this->getJsonObject();
        $disk = config('db-query-logger.drivers.json_file.disk');

        $storage = Storage::disk($disk);

        if (! $storage->exists($fullpath)) {
            $content_array = [$content];
            $storage->put($fullpath, json_encode($content_array, JSON_PRETTY_PRINT));
        } else {
            // Properly parse existing JSON and append
            $existing_content = $storage->get($fullpath);
            $content_array = json_decode($existing_content, true) ?? [];
            $content_array[] = $content;

            $storage->put($fullpath, json_encode($content_array, JSON_PRETTY_PRINT));
        }
    }

    private function getJsonObject()
    {
        $data = [
            'datetime' => now()->format('Y-m-d H:i:s'),
            'query' => $this->query,
            'bindings' => $this->bindings,
            'time' => $this->time,
            'connection' => $this->connection,
            'sql' => $this->sql,
        ];

        $message_formatter = $this->messageFormatter->format();

        $object = $this->compileJsonSchema($message_formatter, $data);

        return $object; // compile and return the formatted message
    }

    private function compileJsonSchema($schema, $data)
    {
        $compiled_schema = [];
        foreach ($schema as $key => $value) {
            if (is_array($value)) {
                $compiled_schema[$key] = $this->compileJsonSchema($value, $data);
            } else {
                $value = $data[$key];
                $compiled_schema[$key] = $value;
            }
        }

        return $compiled_schema;
    }
}
