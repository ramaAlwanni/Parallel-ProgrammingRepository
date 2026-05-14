<?php

namespace App\Jobs;

use App\Services\OrderServiceFactory;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessOrderChunkJob implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    /**
     * @param array<int> $productIds
     */
    public function __construct(
        public array $productIds,
        public bool $optimized,
        public int $chunkIndex,
        public int $totalChunks,
    ) {
    }

    public function handle(OrderServiceFactory $factory): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $service = $factory->make($this->optimized);

        $service->processOrderChunk($this->productIds, [
            'chunk_index' => $this->chunkIndex,
            'total_chunks' => $this->totalChunks,
            'batch_id' => $this->batch()?->id,
            'optimized' => $this->optimized,
        ]);
    }
}
