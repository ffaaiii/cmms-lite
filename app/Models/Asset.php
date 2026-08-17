<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'category', 'location', 'installed_at',
        'condition', 'pm_interval_days', 'last_pm_at', 'status',
    ];

    protected $appends = ['is_deleted'];

    protected function casts(): array
    {
        return [
            'installed_at' => 'date',
            'last_pm_at' => 'date',
        ];
    }

    public function getIsDeletedAttribute(): bool
    {
        return $this->trashed();
    }
}
