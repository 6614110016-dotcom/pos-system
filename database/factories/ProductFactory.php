<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $cost = $this->faker->numberBetween(5, 200);

        return [
            'product_code'  => 'P'.$this->faker->unique()->numberBetween(10000, 99999),
            'barcode'       => $this->faker->unique()->ean13(),
            'name'          => $this->faker->words(3, true),
            'category_id'   => Category::factory(),
            'unit'          => 'ชิ้น',
            'cost_price'    => $cost,
            'price'         => $cost * 1.35,
            'stock_qty'     => $this->faker->numberBetween(0, 100),
            'reorder_level' => 10,
            'status'        => true,
        ];
    }
}
