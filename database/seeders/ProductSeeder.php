<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Product::create([
            'name' => 'minus_sample',
            'description' => 'Another test product with minimal data',
            'price' => 49.99,
            'stock' => 20,
            'store_id' => 1
        ]);

        Store::all()->each(function ($store) {
            Product::factory(5)->create([
                'store_id' => $store->id
            ]);
        });

       
    }
}
