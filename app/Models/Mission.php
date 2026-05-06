<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mission extends Model
{
    protected $fillable = ['skill_domain_id', 'title', 'xp_reward', 'mission_date'];

    protected function casts(): array
    {
        return [
            'mission_date' => 'date',
        ];
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(SkillDomain::class, 'skill_domain_id');
    }

    public function completions(): HasMany
    {
        return $this->hasMany(MissionCompletion::class);
    }
}
