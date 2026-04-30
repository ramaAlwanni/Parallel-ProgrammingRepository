<?php
namespace App\Services;

use App\Interfaces\OrderServiceInterface;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PerformanceMonitorDecorator implements OrderServiceInterface
{
    private OrderServiceInterface $innerService;
    private OrderBatchReportWriter $reportWriter;

    public function __construct(OrderServiceInterface $innerService, ?OrderBatchReportWriter $reportWriter = null)
    {
        $this->innerService = $innerService;
        $this->reportWriter = $reportWriter ?? app(OrderBatchReportWriter::class);
    }

    public function processOrder(int $productId): string
    {
        $traceId = (string) Str::uuid();
        $start = microtime(true);
        $before = $this->snapshot([$productId]);

        Log::info('[Tracing Start]', [
            'trace_id' => $traceId,
            'product_id' => $productId,
            'timestamp' => now(),
            'before' => $before,
        ]);

        try {
            $result = $this->innerService->processOrder($productId);
            $after = $this->snapshot([$productId]);

            $duration = microtime(true) - $start;

            Log::info('[Tracing End]', [
                'trace_id' => $traceId,
                'status' => 'Success',
                'duration' => $duration . 's',
                'result' => $result,
                'before' => $before,
                'after' => $after,
                'delta' => [
                    'stock_change' => $before['stock_sum'] !== null && $after['stock_sum'] !== null
                        ? $before['stock_sum'] - $after['stock_sum']
                        : null,
                ],
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('[Tracing Error]', [
                'trace_id' => $traceId,
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function processOrderChunk(array $productIds, array $context = []): array
    {
        $traceId = (string) Str::uuid();
        $start = microtime(true);
        $before = $this->snapshot($productIds);

        $result = $this->innerService->processOrderChunk($productIds, $context);

        $duration = microtime(true) - $start;
        $after = $this->snapshot($productIds);

        $processed = (int) ($result['processed'] ?? 0);
        $throughput = $duration > 0 ? round($processed / $duration, 2) : $processed;

        $deltaStock = null;
        if ($before['stock_sum'] !== null && $after['stock_sum'] !== null) {
            $deltaStock = $before['stock_sum'] - $after['stock_sum'];
        }

        $payload = [
            'trace_id' => $traceId,
            'mode' => 'batch',
            'chunk_context' => $context,
            'processed_products' => $processed,
            'duration_seconds' => $duration,
            'throughput_items_per_second' => $throughput,
            'before' => $before,
            'after' => $after,
            'delta' => [
                'stock_change' => $deltaStock,
            ],
            'result' => $result,
        ];

        $reportPath = $this->reportWriter->writeChunk($payload);

        Log::info('order.batch.chunk.report_written', [
            'trace_id' => $traceId,
            'report_path' => $reportPath,
            'processed_products' => $processed,
            'duration_seconds' => $duration,
        ]);

        return $payload;
    }

    /**
     * @param array<int> $productIds
     * @return array<string, int|null>
     */
    private function snapshot(array $productIds): array
    {
        if ($productIds === []) {
            return [
                'tracked_products' => 0,
                'stock_sum' => 0,
                'in_stock_products' => 0,
            ];
        }

        try {
            $query = Product::query()->whereIn('id', $productIds);

            return [
                'tracked_products' => count($productIds),
                'stock_sum' => (int) $query->sum('stock'),
                'in_stock_products' => (int) Product::query()
                    ->whereIn('id', $productIds)
                    ->where('stock', '>', 0)
                    ->count(),
            ];
        } catch (\Throwable $exception) {
            return [
                'tracked_products' => count($productIds),
                'stock_sum' => null,
                'in_stock_products' => null,
            ];
        }
    }
}