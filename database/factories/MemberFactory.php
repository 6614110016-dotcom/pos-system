<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Member>
 */
class MemberFactory extends Factory
{
    public function definition(): array
    {
        return [
            'member_code' => 'MB'.$this->faker->unique()->numberBetween(1000, 9999),
            'name'        => $this->faker->name(),
            'phone'       => $this->faker->unique()->numerify('08########'),
            'email'       => $this->faker->unique()->safeEmail(),
            'tier'        => 'silver',
            'points'      => 0,
            'total_spent' => 0,
            'joined_at'   => now(),
            'status'      => true,
        ];
    }
}
