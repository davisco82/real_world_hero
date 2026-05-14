<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Child extends Model
{
    protected $fillable = ['name', 'total_xp'];

    public function completions(): HasMany
    {
        return $this->hasMany(MissionCompletion::class);
    }

    public function achievements(): BelongsToMany
    {
        return $this->belongsToMany(Achievement::class, 'child_achievements')
            ->withPivot('unlocked_at')
            ->withTimestamps();
    }

    public function routineProgresses(): HasMany
    {
        return $this->hasMany(ChildRoutineProgress::class);
    }

    public function level(): int
    {
        return intdiv($this->total_xp, 100) + 1;
    }

    public function xpIntoCurrentLevel(): int
    {
        return $this->total_xp % 100;
    }

    public function xpToNextLevel(): int
    {
        return 100 - $this->xpIntoCurrentLevel();
    }
}
