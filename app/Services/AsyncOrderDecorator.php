<?php

namespace App\Services;

use App\Interfaces\OrderServiceInterface;
use App\Jobs\ProcessOrderFinalization;

class AsyncOrderDecorator implements OrderServiceInterface
{
    private OrderServiceInterface $innerService;

    public function __construct(OrderServiceInterface $innerService)
    {
        $this->innerService = $innerService;
    }

    public function processOrder(int $productId): array|string
    {
        // Synchronization Point: execute critical order workflow synchronously first.
        $result = $this->innerService->processOrder($productId);

        // Defer non-critical finalization work to the queue for better responsiveness.
        ProcessOrderFinalization::dispatch($productId)->onQueue('default');

        return $result;
    }

    public function processOrderChunk(array $productIds, array $context = []): array
    {
        // Synchronization Point: execute critical batch order workflow synchronously first.
        $result = $this->innerService->processOrderChunk($productIds, $context);

        // Defer finalization tasks for each product to the queue for better throughput.
        foreach ($productIds as $productId) {
            ProcessOrderFinalization::dispatch($productId)->onQueue('default');
        }

        return $result;
    }
}
