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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DailyMissionController extends Controller
{
    public function childDashboard(): View
    {
        $user = Auth::user();
        abort_unless($user, 403);

        $child = null;
        if ($user->role === 'child') {
            $child = Child::query()->findOrFail($user->child_id);
        } elseif ($user->role === 'parent') {
            $parentChildIds = \App\Models\User::query()
                ->where('role', 'child')
                ->where('parent_user_id', $user->id)
                ->whereNotNull('child_id')
                ->pluck('child_id');

            $requestedChildId = request()->integer('child_id');
            if ($requestedChildId) {
                if (! $parentChildIds->contains($requestedChildId)) {
                    throw new NotFoundHttpException();
                }
                $child = Child::query()->findOrFail($requestedChildId);
            } else {
                $firstChildId = $parentChildIds->first();
                abort_unless($firstChildId, 404);
                $child = Child::query()->findOrFail($firstChildId);
            }
        } else {
            abort(403);
        }

        $this->ensureFirstMissionSetForToday();

        $missions = Mission::query()
            ->with(['domain', 'completions' => function ($q) use ($child) {
                $q->where('child_id', $child->id);
            }])
            ->whereDate('mission_date', now()->toDateString())
            ->get();

        $achievementTitles = $child->achievements()->pluck('title');
        $week = $this->buildWeeklyOverview($child);

        return view('mvp.child', compact('child', 'missions', 'achievementTitles', 'week'));
    }

    public function parentDashboard(): View|RedirectResponse
    {
        $user = Auth::user();
        abort_unless($user, 403);
        if ($user->role === 'child') {
            return redirect()->route('mvp.child');
        }
        abort_unless($user->role === 'parent', 403);

        $children = \App\Models\User::query()
            ->where('role', 'child')
            ->where('parent_user_id', $user->id)
            ->with('child')
            ->get()
            ->pluck('child')
            ->filter()
            ->values();

        $childIds = $children->pluck('id');

        $pending = MissionCompletion::query()
            ->with(['mission.domain', 'child'])
            ->where('status', 'pending_parent')
            ->whereIn('child_id', $childIds)
            ->latest('completed_at')
            ->get();

        $calendarChild = $children->first();
        $week = $this->buildWeeklyOverview($calendarChild);

        return view('mvp.parent', compact('pending', 'children', 'week', 'calendarChild'));
    }

    public function completeMission(Mission $mission): RedirectResponse
    {
        abort_unless(Auth::user()?->role === 'child', 403);

        $child = Child::query()->findOrFail(Auth::user()->child_id);

        $completion = MissionCompletion::query()->firstOrNew([
            'mission_id' => $mission->id,
            'child_id' => $child->id,
        ]);

        $completion->fill([
            'status' => 'pending_parent',
            'completed_at' => now(),
            'approved_at' => null,
        ]);
        $completion->save();

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

    private function buildWeeklyOverview(?Child $child): array
    {
        $start = now()->startOfWeek(\Carbon\Carbon::MONDAY);
        $end = now()->endOfWeek(\Carbon\Carbon::SUNDAY);
        $weekLabel = $start->format('j.n.') . ' - ' . $end->format('j.n.Y');

        $possibleByDate = Mission::query()
            ->whereBetween('mission_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('mission_date, SUM(xp_reward) as possible_xp')
            ->groupBy('mission_date')
            ->pluck('possible_xp', 'mission_date');

        $earnedByDate = collect();
        if ($child) {
            $earnedByDate = MissionCompletion::query()
                ->join('missions', 'missions.id', '=', 'mission_completions.mission_id')
                ->where('mission_completions.child_id', $child->id)
                ->where('mission_completions.status', 'approved')
                ->whereBetween('missions.mission_date', [$start->toDateString(), $end->toDateString()])
                ->selectRaw('missions.mission_date as mission_date, SUM(missions.xp_reward) as earned_xp')
                ->groupBy('missions.mission_date')
                ->pluck('earned_xp', 'mission_date');
        }

        $days = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $key = $cursor->toDateString();
            $possible = (int) ($possibleByDate[$key] ?? 0);
            $earned = (int) ($earnedByDate[$key] ?? 0);
            $percent = $possible > 0 ? (int) round(($earned / $possible) * 100) : 0;

            $days[] = [
                'date_label' => $cursor->format('j.n.'),
                'weekday' => $cursor->locale('cs')->translatedFormat('D'),
                'earned' => $earned,
                'possible' => $possible,
                'percent' => min(100, max(0, $percent)),
            ];
            $cursor->addDay();
        }

        return [
            'week_label' => $weekLabel,
            'days' => $days,
            'week_earned' => array_sum(array_column($days, 'earned')),
            'week_possible' => array_sum(array_column($days, 'possible')),
        ];
    }
}
