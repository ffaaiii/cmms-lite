<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AssetFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'category' => fake()->randomElement(['turbine', 'well', 'pipe', 'cooling_tower', 'other']),
            'location' => fake()->city(),
            'installed_at' => fake()->date(),
            'condition' => 'good',
            'pm_interval_days' => fake()->numberBetween(30, 180),
            'status' => 'active',
        ];
    }
}
