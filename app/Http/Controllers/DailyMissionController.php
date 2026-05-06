<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\Child;
use App\Models\Mission;
use App\Models\MissionCompletion;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DailyMissionController extends Controller
{
    public function childDashboard(): View
    {
        $child = Child::query()->firstOrFail();

        $missions = Mission::query()
            ->with(['domain', 'completions' => function ($q) use ($child) {
                $q->where('child_id', $child->id);
            }])
            ->whereDate('mission_date', now()->toDateString())
            ->get();

        $achievementTitles = $child->achievements()->pluck('title');

        return view('mvp.child', compact('child', 'missions', 'achievementTitles'));
    }

    public function parentDashboard(): View
    {
        $pending = MissionCompletion::query()
            ->with(['mission.domain', 'child'])
            ->where('status', 'pending_parent')
            ->latest('completed_at')
            ->get();

        return view('mvp.parent', compact('pending'));
    }

    public function completeMission(Mission $mission): RedirectResponse
    {
        $child = Child::query()->firstOrFail();

        MissionCompletion::query()->firstOrCreate([
            'mission_id' => $mission->id,
            'child_id' => $child->id,
        ], [
            'status' => 'pending_parent',
            'completed_at' => now(),
        ]);

        return back();
    }

    public function approveCompletion(MissionCompletion $completion): RedirectResponse
    {
        if ($completion->status !== 'pending_parent') {
            return back();
        }

        $completion->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        $child = $completion->child;
        $child->increment('total_xp', $completion->mission->xp_reward);

        $achievement = Achievement::query()->where('code', 'first_mission_approved')->first();
        if ($achievement && ! $child->achievements()->where('achievement_id', $achievement->id)->exists()) {
            $child->achievements()->attach($achievement->id, ['unlocked_at' => now()]);
        }

        return back();
    }
}
