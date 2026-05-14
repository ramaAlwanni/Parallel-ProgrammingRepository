<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log; 

class ProcessOrderFinalization implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $productId;
    public int $tries = 3; 

    public function __construct(int $productId)
    {
        $this->productId = $productId;
    }

    public function handle(): void
    {
        Log::info("Job Started: Finalizing order for Product ID: {$this->productId}");

        sleep(3); 

        Log::info("Job Finished: Order finalized for Product ID: {$this->productId}");
    }

  
    public function failed(\Throwable $exception): void
    {
        Log::error("Job Failed: Could not finalize order {$this->productId}. Error: " . $exception->getMessage());
    }
}