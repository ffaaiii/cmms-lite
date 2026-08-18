<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['work_order_id', 'user_id', 'from_status', 'to_status', 'note'];

    protected $attributes = ['created_at' => null];

    protected static function booted(): void
    {
        static::creating(fn ($log) => $log->created_at ??= now());
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}