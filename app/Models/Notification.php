<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'type', 'message', 'related_work_order_id', 'is_read'];

    protected $casts = ['is_read' => 'boolean'];

    protected $attributes = ['created_at' => null];

    protected static function booted(): void
    {
        static::creating(fn ($notif) => $notif->created_at ??= now());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function relatedWorkOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class, 'related_work_order_id');
    }
}
