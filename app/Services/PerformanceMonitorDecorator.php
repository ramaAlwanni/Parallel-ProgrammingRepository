<?php
namespace App\Services;

use App\Interfaces\OrderServiceInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PerformanceMonitorDecorator implements OrderServiceInterface {
    private $innerService;

    public function __construct(OrderServiceInterface $innerService) {
        $this->innerService = $innerService;
    }

    public function processOrder($productId) {
        $traceId = (string) Str::uuid();
        $start = microtime(true);

        Log::info("[Tracing Start] ID: $traceId", [
            'product_id' => $productId,
            'timestamp' => now()
        ]);

        try {
            $result = $this->innerService->processOrder($productId);

            $duration = microtime(true) - $start;

            Log::info("[Tracing End] ID: $traceId", [
                'status' => 'Success',
                'duration' => $duration . 's'
            ]);

            return [
                'result' => $result,
                'time' => $duration,
                'trace_id' => $traceId
            ];

        } catch (\Exception $e) {
            Log::error("[Tracing Error] ID: $traceId", [
                'message' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}