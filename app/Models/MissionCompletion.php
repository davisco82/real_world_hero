<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MissionCompletion extends Model
{
    protected $fillable = [
        'mission_id',
        'child_id',
        'status',
        'completed_at',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class);
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }
}
