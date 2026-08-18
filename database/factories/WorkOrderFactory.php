<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkOrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'asset_id' => Asset::factory(),
            'created_by' => User::factory()->state([
                'role_id' => fn () => Role::where('slug', 'supervisor')->first()?->id
                    ?? Role::factory(),
            ]),
            'type' => fake()->randomElement(['preventive', 'corrective']),
            'priority' => 'normal',
            'status' => 'draft',
        ];
    }

    // State helper supaya test tidak perlu assign manual tiap kali
    // butuh WO yang sudah punya teknisi.
    public function assignedTo(User $technician): static
    {
        return $this->state(fn () => [
            'assigned_to' => $technician->id,
            'status' => 'assigned',
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attrs) => [
            'status' => 'in_progress',
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attrs) => [
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }
}