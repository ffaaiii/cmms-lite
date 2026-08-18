<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderPart extends Model
{
    public $timestamps = false; // hanya created_at

    protected $fillable = ['work_order_id', 'part_name', 'quantity', 'unit'];

    protected $attributes = ['created_at' => null];

    protected static function booted(): void
    {
        static::creating(fn ($part) => $part->created_at ??= now());
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }
}