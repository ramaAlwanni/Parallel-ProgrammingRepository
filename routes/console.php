<?php

use App\Jobs\ProcessOrderChunkJob;
use App\Models\Product;
use Illuminate\Bus\Batch;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('orders:process-daily {--optimized=true} {--chunk=200} {--queue=default}', function () {
    $chunkSize = max(1, (int) $this->option('chunk'));
    $queueName = (string) $this->option('queue');
    $optimized = filter_var((string) $this->option('optimized'), FILTER_VALIDATE_BOOLEAN);

    $productIds = Product::query()
        ->where('stock', '>', 0)
        ->orderBy('id')
        ->pluck('id')
        ->all();

    if ($productIds === []) {
        $this->info('No in-stock products found. Nothing to process.');
        return self::SUCCESS;
    }

    $chunks = array_chunk($productIds, $chunkSize);
    $jobs = [];

    foreach ($chunks as $index => $chunkIds) {
        $jobs[] = (new ProcessOrderChunkJob(
            productIds: $chunkIds,
            optimized: $optimized,
            chunkIndex: $index + 1,
            totalChunks: count($chunks),
        ))->onQueue($queueName);
    }

    $batchName = sprintf('daily-order-processing-%s', now()->toDateString());

    $batch = Bus::batch($jobs)
        ->name($batchName)
        ->then(function (Batch $batch): void {
            Log::info('order.batch.completed', [
                'batch_id' => $batch->id,
                'processed_jobs' => $batch->processedJobs(),
                'failed_jobs' => $batch->failedJobs,
            ]);
        })
        ->catch(function (Batch $batch, \Throwable $exception): void {
            Log::error('order.batch.failed', [
                'batch_id' => $batch->id,
                'message' => $exception->getMessage(),
            ]);
        })
        ->dispatch();

    Log::info('order.batch.dispatched', [
        'batch_id' => $batch->id,
        'optimized' => $optimized,
        'chunk_size' => $chunkSize,
        'chunk_count' => count($chunks),
        'candidate_products' => count($productIds),
    ]);

    $this->info("Dispatched batch {$batch->id} with ".count($chunks).' chunk jobs.');

    return self::SUCCESS;
})->purpose('Dispatch daily order processing jobs in chunks');

Schedule::command('orders:process-daily --optimized=true --chunk=200')
    ->dailyAt('01:00')
    ->withoutOverlapping();
