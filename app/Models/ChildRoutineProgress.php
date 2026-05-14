<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChildRoutineProgress extends Model
{
    protected $table = 'child_routine_progress';

    protected $fillable = [
        'child_id',
        'routine_template_id',
        'approved_count',
        'current_streak',
        'best_streak',
        'completed_cycles',
        'window_start',
        'last_approved_date',
        'last_completed_at',
    ];

    protected function casts(): array
    {
        return [
            'window_start' => 'date',
            'last_approved_date' => 'date',
            'last_completed_at' => 'datetime',
        ];
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    public function routine(): BelongsTo
    {
        return $this->belongsTo(RoutineTemplate::class, 'routine_template_id');
    }
}
