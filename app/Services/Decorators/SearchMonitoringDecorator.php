<?php

namespace App\Services\Decorators;

use App\Interfaces\ProductSearchInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class SearchMonitoringDecorator implements ProductSearchInterface
{
    protected ProductSearchInterface $searchService;

    public function __construct(ProductSearchInterface $searchService)
    {
        $this->searchService = $searchService;
    }
//-----------------------------------------------------------------------------------------
    public function search(string $keyword, int $limit = 5)
    {
        $traceId = (string) Str::uuid();
        $start = microtime(true);

        Log::info("[Tracing Start] ID: $traceId", [
            'keyword' => $keyword,
            'limit' => $limit,
            'timestamp' => now()
        ]);

        try {
            $result = $this->searchService->search($keyword, $limit);

            $duration = microtime(true) - $start;

            Log::info("[Tracing End] ID: $traceId", [
                'status' => 'Completed',
                'duration' => $duration . 's'
            ]);

            return array_merge($result, [
                'trace_id' => $traceId,
                'execution_time' => round($duration, 4)
            ]);

        } catch (\Exception $e) {
            Log::error("[Tracing Error] ID: $traceId", [
                'message' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
