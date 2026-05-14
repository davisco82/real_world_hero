<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoutineTemplate extends Model
{
    protected $fillable = [
        'skill_domain_id',
        'title',
        'description',
        'period',
        'base_xp',
        'bonus_xp',
        'goal_type',
        'goal_target',
        'window_days',
        'active_from',
        'active_until',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'active_from' => 'date',
            'active_until' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(SkillDomain::class, 'skill_domain_id');
    }

    public function missions(): HasMany
    {
        return $this->hasMany(Mission::class);
    }

    public function progresses(): HasMany
    {
        return $this->hasMany(ChildRoutineProgress::class);
    }
}
