<?php
namespace App\Services;

use App\Interfaces\OrderServiceInterface;
use App\Models\Product;

class UnsafeOrderService implements OrderServiceInterface
{

    public function processOrder(int $productId): string
    {

        $product = Product::find($productId);

        if (! $product) {
            return 'Product Not Found';
        }

        if ($product->stock > 0) {
            usleep(500000);

            $product->stock -= 1;
            $product->save();
            return 'Success (Unsafe)';
        }

        return 'Out of Stock';
    }

    public function processOrderChunk(array $productIds, array $context = []): array
    {
        $summary = [
            'processed' => 0,
            'success' => 0,
            'not_found' => 0,
            'out_of_stock' => 0,
            'failed' => 0,
        ];

        foreach ($productIds as $productId) {
            try {
                $status = $this->processOrder((int) $productId);
                $summary['processed']++;

                if (str_contains($status, 'Success')) {
                    $summary['success']++;
                } elseif ($status === 'Product Not Found') {
                    $summary['not_found']++;
                } elseif ($status === 'Out of Stock') {
                    $summary['out_of_stock']++;
                } else {
                    $summary['failed']++;
                }
            } catch (\Throwable $exception) {
                $summary['processed']++;
                $summary['failed']++;
            }
        }

        return $summary;
    }
}