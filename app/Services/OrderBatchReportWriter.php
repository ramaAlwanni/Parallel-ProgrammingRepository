<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OrderBatchReportWriter
{
    public function writeChunk(array $payload): string
    {
        $date = now()->toDateString();
        $directory = 'reports/order-batches';
        $fileName = sprintf('%s-%s.jsonl', 'order-batch', $date);
        $path = sprintf('%s/%s', $directory, $fileName);

        // Ensure directory exists before appending
        if (! Storage::disk('local')->exists($directory)) {
            Storage::disk('local')->makeDirectory($directory);
        }

        $record = array_merge([
            'record_type' => 'chunk',
            'recorded_at' => now()->toIso8601String(),
            'report_id' => (string) Str::uuid(),
        ], $payload);

        Storage::disk('local')->append($path, json_encode($record, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        return $path;
    }
}