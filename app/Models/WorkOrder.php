<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'assigned_to',
        'created_by',
        'approved_by',
        'type',
        'priority',
        'status',
        'description',
        'rejection_count',
        'rejection_note',
        'is_escalated',
        'scheduled_at',
        'completed_at',
        'closed_at',
    ];

    protected $casts = [
        'is_escalated' => 'boolean',
        'scheduled_at' => 'date',
        'completed_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function parts(): HasMany
    {
        return $this->hasMany(WorkOrderPart::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(WorkOrderLog::class)->latest();
    }
}