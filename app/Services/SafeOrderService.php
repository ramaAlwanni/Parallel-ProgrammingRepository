<?php
namespace App\Services;

use App\Interfaces\OrderServiceInterface;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class SafeOrderService implements OrderServiceInterface {

    public function processOrder($productId) {

        return DB::transaction(function () use ($productId) {

            $product = Product::where('id', $productId)->lockForUpdate()->first();
             if(! $product)
            {
                return "Product Not Found";
            }
            if ($product && $product->stock > 0) {
                $product->decrement('stock');
                return "Success (Safe)";
            }
            return "Out of Stock";
        });
    }
}
