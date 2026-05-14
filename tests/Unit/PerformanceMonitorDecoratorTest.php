<?php

namespace Tests\Unit;

use App\Interfaces\OrderServiceInterface;
use App\Services\PerformanceMonitorDecorator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class PerformanceMonitorDecoratorTest extends TestCase
{
    public function test_it_returns_before_after_payload_for_chunk_processing(): void
    {
        Storage::fake('local');
        Log::spy();

        $inner = new class implements OrderServiceInterface {
            public function processOrder(int $productId): string
            {
                return 'Success (Fake)';
            }

            public function processOrderChunk(array $productIds, array $context = []): array
            {
                return [
                    'processed' => count($productIds),
                    'success' => count($productIds),
                    'not_found' => 0,
                    'out_of_stock' => 0,
                    'failed' => 0,
                ];
            }
        };

        $decorator = new PerformanceMonitorDecorator($inner);
        $payload = $decorator->processOrderChunk([10, 11, 12], ['chunk_index' => 1]);

        $this->assertArrayHasKey('before', $payload);
        $this->assertArrayHasKey('after', $payload);
        $this->assertArrayHasKey('delta', $payload);
        $this->assertArrayHasKey('throughput_items_per_second', $payload);
        $this->assertSame(3, $payload['processed_products']);

        Storage::disk('local')->assertExists('reports/order-batches/order-batch-' . now()->toDateString() . '.jsonl');

        $contents = Storage::disk('local')->get('reports/order-batches/order-batch-' . now()->toDateString() . '.jsonl');

        $this->assertStringContainsString('"record_type":"chunk"', $contents);
        $this->assertStringContainsString('"before"', $contents);
        $this->assertStringContainsString('"after"', $contents);
        $this->assertStringContainsString('"throughput_items_per_second"', $contents);

        Log::shouldHaveReceived('info')->withArgs(function (string $message, array $context): bool {
            return $message === 'order.batch.chunk.report_written'
                && array_key_exists('report_path', $context)
                && array_key_exists('processed_products', $context);
        })->once();
    }
}
