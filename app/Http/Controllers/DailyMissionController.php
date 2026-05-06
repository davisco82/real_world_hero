<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\Child;
use App\Models\Mission;
use App\Models\MissionCompletion;
use App\Models\SkillDomain;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DailyMissionController extends Controller
{
    public function childDashboard(): View
    {
        abort_unless(Auth::user()?->role === 'child', 403);

        $child = Child::query()->findOrFail(Auth::user()->child_id);
        $this->ensureFirstMissionSetForToday();

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
        abort_unless(Auth::user()?->role === 'parent', 403);

        $pending = MissionCompletion::query()
            ->with(['mission.domain', 'child'])
            ->where('status', 'pending_parent')
            ->latest('completed_at')
            ->get();

        return view('mvp.parent', compact('pending'));
    }

    public function completeMission(Mission $mission): RedirectResponse
    {
        abort_unless(Auth::user()?->role === 'child', 403);

        $child = Child::query()->findOrFail(Auth::user()->child_id);

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
        abort_unless(Auth::user()?->role === 'parent', 403);

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

    private function ensureFirstMissionSetForToday(): void
    {
        $today = now()->toDateString();

        $missionTemplates = [
            ['Přežití', 'Připrav si školní tašku', 20],
            ['Práce s časem', 'Dokonči domácí úkol před večeří', 30],
            ['Zdraví a energie', '20 minut pohybu', 25],
        ];

        foreach ($missionTemplates as [$domainName, $title, $xp]) {
            $domain = SkillDomain::query()->where('name', $domainName)->first();
            if (! $domain) {
                continue;
            }

            Mission::query()->firstOrCreate([
                'skill_domain_id' => $domain->id,
                'title' => $title,
                'mission_date' => $today,
            ], [
                'xp_reward' => $xp,
            ]);
        }
    }
}
