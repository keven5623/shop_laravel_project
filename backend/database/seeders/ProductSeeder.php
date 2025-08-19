<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::truncate();

        Product::insert([
            [
                'name' => '高級咖啡豆',
                'description' => '來自哥倫比亞的精品咖啡豆，香氣濃郁。',
                'price' => 450.00,
                'stock' => 100,
                'category_id' => 3
            ],
            [
                'name' => '舒適辦公椅',
                'description' => '符合人體工學，長時間工作不疲勞。',
                'price' => 3200.00,
                'stock' => 20,
                'category_id' => 1
            ],
            [
                'name' => '無線藍牙耳機',
                'description' => '降噪效果佳，續航力強。',
                'price' => 980.00,
                'stock' => 50,
                'category_id' => 1
            ],
        ]);
    }
}
