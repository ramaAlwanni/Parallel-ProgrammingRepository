<?php
namespace App\Services;

use App\Interfaces\OrderServiceInterface;
use App\Models\Product;

class UnsafeOrderService implements OrderServiceInterface {

    public function processOrder($productId) {

        $product = Product::find($productId);

        if(! $product) {
            return "Product Not Found";
        }

        if ($product->stock > 0) {
            usleep(500000);

            $product->stock -= 1;
            $product->save();
            return "Success (Unsafe)";
        }
        return "Out of Stock";
    }
}