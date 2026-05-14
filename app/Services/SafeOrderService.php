<?php

namespace App\Services;

use App\Interfaces\OrderServiceInterface;
use App\Models\Product;
use App\Models\Order ;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SafeOrderService implements OrderServiceInterface

{
    public function processOrder(int $productId): string {
        return DB::transaction(function () use ($productId) {
        $product = $this->getProductWithLock($productId);
        
        if (!$product || $product->stock <= 0) {
            return "Failed";
        }

        $this->updateStock($product);
        
        $order = $this->createOrder($product);
        
        $this->processPayment($order);

        return "Success";
    });
}

    private function getProductWithLock($id) { 
    return Product::where('id', $id)->lockForUpdate()->first(); 
    }

    private function updateStock($product) { 
    $product->decrement('stock'); 
    }
    private function createOrder($product) {
    return Order::create([
        'user_id' => $this->resolveUserId(),
        'product_id' => $product->id,
    ]);
}

    private function resolveUserId(): int
    {
        return (int) User::query()->firstOrCreate(
            ['email' => 'system@example.com'],
            [
                'name' => 'System User',
                'password' => Hash::make(Str::random(32)),
            ]
        )->id;
    }

private function processPayment($order) {
    return Payment::create([
        'order_id' => $order->id,
        'amount' => 100, //for testing
        'transaction_id' => uniqid('PAY-'),
        'status' => 'paid',
    ]);
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
            $summary['processed']++;

            $result = $this->processOrder((int) $productId);

            if ($result === 'Success') {
                $summary['success']++;
                continue;
            }

            if ($result === 'Out of Stock') {
                $summary['out_of_stock']++;
                continue;
            }

            if ($result === 'Product Not Found') {
                $summary['not_found']++;
                continue;
            }

            $summary['failed']++;
        }

        return $summary;
    }
 
}