<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\Child;
use App\Models\ChildRoutineProgress;
use App\Models\Mission;
use App\Models\MissionCompletion;
use App\Models\RoutineTemplate;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
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
            ->with(['domain', 'routineTemplate', 'completions' => function ($q) use ($child) {
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
        $this->applyRoutineBonusIfReached($child, $completion->mission);

        $achievement = Achievement::query()->where('code', 'first_mission_approved')->first();
        if ($achievement && ! $child->achievements()->where('achievement_id', $achievement->id)->exists()) {
            $child->achievements()->attach($achievement->id, ['unlocked_at' => now()]);
        }

        return back();
    }

    private function ensureFirstMissionSetForToday(): void
    {
        $today = now()->toDateString();
        $activeRoutines = RoutineTemplate::query()
            ->where('is_active', true)
            ->whereDate('active_from', '<=', $today)
            ->where(function (Builder $query) use ($today) {
                $query->whereNull('active_until')
                    ->orWhereDate('active_until', '>=', $today);
            })
            ->with('domain')
            ->get();

        foreach ($activeRoutines as $routine) {
            if (! $routine->domain) {
                continue;
            }

            Mission::query()->firstOrCreate([
                'routine_template_id' => $routine->id,
                'skill_domain_id' => $routine->skill_domain_id,
                'title' => $routine->title,
                'mission_date' => $today,
            ], [
                'xp_reward' => $routine->base_xp,
            ]);
        }
    }

    private function applyRoutineBonusIfReached(Child $child, Mission $mission): void
    {
        $routine = $mission->routineTemplate;
        if (! $routine) {
            return;
        }

        $today = Carbon::today();

        $progress = ChildRoutineProgress::query()->firstOrCreate([
            'child_id' => $child->id,
            'routine_template_id' => $routine->id,
        ]);

        if ($progress->last_approved_date?->isSameDay($today)) {
            return;
        }

        if ($routine->goal_type === 'streak') {
            $isConsecutive = $progress->last_approved_date?->isSameDay($today->copy()->subDay()) ?? false;
            $progress->current_streak = $isConsecutive ? ($progress->current_streak + 1) : 1;
            $progress->best_streak = max($progress->best_streak, $progress->current_streak);
            $progress->approved_count++;
            $progress->last_approved_date = $today;

            if ($progress->current_streak >= $routine->goal_target) {
                $child->increment('total_xp', $routine->bonus_xp);
                $progress->completed_cycles++;
                $progress->current_streak = 0;
                $progress->approved_count = 0;
                $progress->last_completed_at = now();
            }

            $progress->save();

            return;
        }

        $windowDays = max(1, (int) ($routine->window_days ?? 1));
        if (! $progress->window_start) {
            $progress->window_start = $today;
        }

        $windowEnd = $progress->window_start->copy()->addDays($windowDays - 1);
        if ($today->greaterThan($windowEnd)) {
            $progress->window_start = $today;
            $progress->approved_count = 0;
            $progress->current_streak = 0;
        }

        $progress->approved_count++;
        $progress->last_approved_date = $today;
        $progress->current_streak = 0;

        if ($progress->approved_count >= $routine->goal_target) {
            $child->increment('total_xp', $routine->bonus_xp);
            $progress->completed_cycles++;
            $progress->approved_count = 0;
            $progress->window_start = $today->copy()->addDay();
            $progress->last_completed_at = now();
        }

        $progress->save();
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
