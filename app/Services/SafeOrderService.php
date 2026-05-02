<?php
namespace App\Services;

use App\Interfaces\OrderServiceInterface;
use App\Models\Product;
use App\Models\Order ;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class SafeOrderService implements OrderServiceInterface

{
    public function processOrder($productId) {
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
        'user_id' => 1, //for testing
        'product_id' => $product->id,
    ]);
}

private function processPayment($order) {
    return Payment::create([
        'order_id' => $order->id,
        'amount' => 100, //for testing
        'transaction_id' => uniqid('PAY-'),
        'status' => 'paid',
    ]);
}
 
}