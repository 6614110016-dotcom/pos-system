<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code'   => 'CAT'.$this->faker->unique()->numberBetween(100, 999),
            'name'   => $this->faker->unique()->word(),
            'status' => true,
        ];
    }
}
