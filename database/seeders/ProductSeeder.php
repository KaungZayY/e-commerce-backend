<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $excluded = [
            'Mobile Phones', 'Accessories', 'Electronics', 'Networking', 'Wholesale',
            'Telecom & Mobile Devices', 'General Wholesale',
        ];

        $categories = Category::whereNotIn('category_name', $excluded)->get();

        foreach ($categories as $category) {
            // Create 5 products for each subcategory
            Product::factory()->count(5)->create([
                'category_id' => $category->id,
            ]);
        }
    }
}
