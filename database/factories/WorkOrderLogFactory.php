<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderLog;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkOrderLogFactory extends Factory
{
    protected $model = WorkOrderLog::class;

    public function definition(): array
    {
        return [
            'work_order_id' => WorkOrder::factory(),
            'user_id' => User::factory(),
            'from_status' => 'open',
            'to_status' => 'in_progress',
            'note' => 'Log automatis dari factory',
            'created_at' => now(),
        ];
    }
}
