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
        Store::all()->each(function ($store) {
            Product::factory(5)->create([
                'store_id' => $store->id
            ]);
        });
        //Race Condition Product for testing purposes 
        Product::create([
            'name' => 'Test product',
            'stock' => 1,
            'store_id' => 1
        ]);
    }
}
