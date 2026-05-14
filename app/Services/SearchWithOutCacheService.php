<?php

namespace App\Services;

use App\Models\Product;
use App\Interfaces\ProductSearchInterface;

class SearchWithOutCacheService implements ProductSearchInterface
{
    public function search(string $keyword, int $limit = 5): array
    {
        $products = Product::where('name', 'LIKE', "{$keyword}%")
                            ->orWhere('description', 'LIKE', "{$keyword}%")
                            ->take($limit)
                            ->get();

        return [
            'products' => $products->pluck('name'),
        ];
    }
}
