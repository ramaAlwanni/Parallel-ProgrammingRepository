<?php

namespace Tests\Feature;

use App\Jobs\ProcessOrderChunkJob;
use App\Models\Product;
use Illuminate\Bus\PendingBatch;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyOrderBatchCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_daily_batch_command_dispatches_chunk_jobs(): void
    {
        Storage::fake('local');
        Product::factory()->count(5)->create(['stock' => 5]);

        Bus::fake();

        $exitCode = Artisan::call('orders:process-daily', [
            '--optimized' => 'true',
            '--chunk' => 2,
        ]);

        $this->assertSame(0, $exitCode);

        Bus::assertBatched(function (PendingBatch $batch): bool {
            return $batch->jobs->count() === 3
                && $batch->jobs->every(fn (object $job) => $job instanceof ProcessOrderChunkJob);
        });
    }
}
