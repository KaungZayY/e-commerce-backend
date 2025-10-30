<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Category::count() === 0) {
            // Top-level sections
            $mobilePhones = Category::create([
                'category_name' => 'Mobile Phones',
                'parent_id' => null,
            ]);

            $accessories = Category::create([
                'category_name' => 'Accessories',
                'parent_id' => null,
            ]);

            $electronics = Category::create([
                'category_name' => 'Electronics',
                'parent_id' => null,
            ]);

            $networking = Category::create([
                'category_name' => 'Networking',
                'parent_id' => null,
            ]);


            $wholesale = Category::create([
                'category_name' => 'Wholesale',
                'parent_id' => null,
            ]);

            // Children for Mobile Phones
            Category::create([
                'category_name' => 'Smart Phones',
                'parent_id' => $mobilePhones->id,
            ]);
            Category::create([
                'category_name' => 'Feature Phones',
                'parent_id' => $mobilePhones->id,
            ]);
            Category::create([
                'category_name' => 'Refurbished Phones',
                'parent_id' => $mobilePhones->id,
            ]);

            // Children for Accessories
            Category::create([
                'category_name' => 'Chargers',
                'parent_id' => $accessories->id,
            ]);
            Category::create([
                'category_name' => 'Earphones',
                'parent_id' => $accessories->id,
            ]);
            Category::create([
                'category_name' => 'Cases',
                'parent_id' => $accessories->id,
            ]);
            Category::create([
                'category_name' => 'Power Banks',
                'parent_id' => $accessories->id,
            ]);

            // Children for Electronics
            Category::create([
                'category_name' => 'Smart Watches',
                'parent_id' => $electronics->id,
            ]);
            Category::create([
                'category_name' => 'Tablets',
                'parent_id' => $electronics->id,
            ]);
            Category::create([
                'category_name' => 'Bluetooth Speakers',
                'parent_id' => $electronics->id,
            ]);
            Category::create([
                'category_name' => 'Cameras',
                'parent_id' => $electronics->id,
            ]);

            // Children for Networking
            Category::create([
                'category_name' => 'Routers',
                'parent_id' => $networking->id,
            ]);
            Category::create([
                'category_name' => 'Wi-Fi Devices',
                'parent_id' => $networking->id,
            ]);
            Category::create([
                'category_name' => 'Cables',
                'parent_id' => $networking->id,
            ]);
            Category::create([
                'category_name' => 'Modems',
                'parent_id' => $networking->id,
            ]);

            // Children for Wholesale
            Category::create([
                'category_name' => 'Telecom & Mobile Devices',
                'parent_id' => $wholesale->id,
            ]);
            Category::create([
                'category_name' => 'General Wholesale',
                'parent_id' => $wholesale->id,
            ]);
        }
    }
}
