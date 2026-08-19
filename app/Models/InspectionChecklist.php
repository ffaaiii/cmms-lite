<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspectionChecklist extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'asset_id', 'inspected_by', 'reviewed_by', 'generated_work_order_id',
        'condition_found', 'notes', 'status',
    ];

    protected $attributes = ['created_at' => null];

    protected static function booted(): void
    {
        static::creating(fn ($checklist) => $checklist->created_at ??= now());
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspected_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function generatedWorkOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class, 'generated_work_order_id');
    }
}
