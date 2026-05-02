<?php
namespace App\Services;
use App\Interfaces\OrderServiceInterface;
use App\Models\Product;

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
                'user_id' => 1, 
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
}
