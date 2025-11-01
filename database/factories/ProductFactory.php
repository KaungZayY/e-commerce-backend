<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'product_name' => $this->faker->words(3, true),
            'images' => json_encode([
                "files/products/images/iphone17promax_6905d352ec114.webp",
                "files/products/images/iphone17_pro_max_6905d352f39c0.jpeg",
            ]),
            'price' => $this->faker->randomFloat(2, 50, 1500),
            'qty' => $this->faker->numberBetween(5, 100),
            'description' => $this->faker->paragraph(),
            'is_popular' => $this->faker->boolean(30),
            'moq' => null,
            'discount_type' => $this->faker->randomElement([null, 'percentage', 'amount']),
            'discount_amount' => $this->faker->randomFloat(2, 0, 50),
            'created_by' => 1,
            'category_id' => null, // Will be set in seeder
        ];
    }
}
