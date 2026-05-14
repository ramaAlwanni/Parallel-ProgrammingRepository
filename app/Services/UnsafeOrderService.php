<?php
namespace App\Services;
use App\Interfaces\OrderServiceInterface;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UnsafeOrderService implements OrderServiceInterface
{

    public function processOrder(int $productId): string
    {

        $product = Product::find($productId);

        if(! $product)
            {
                return "Product Not Found";
            }
        
        if ($product->stock >0) {
            usleep(500000); 
         
            $product->stock -= 1;  // decrement stock in unsafe way
            $product->save();

            //create order without transaction 
            $order = Order::create([
                'product_id' => $productId,
                'user_id' => $this->resolveUserId(), 
                'status' => 'completed',
                'total_price' => $product->price
            ]);
            
            //create payment without trasaction 
            Payment::create([
                'order_id' => $order->id,
                'amount' => $product->price,
                'status' => 'paid',
                'transaction_id' => uniqid('PAY-UNSAFE-')
            ]);

            return "Success (Unsafe)";
        }
        return "Out of Stock";
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

            if ($result === 'Success (Unsafe)') {
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
