<aside class="relative overflow-hidden rounded-3xl border border-sky-400/20 bg-slate-900/90 p-4 shadow-[0_0_30px_rgba(56,189,248,0.12)] lg:sticky lg:top-4">
    <div class="pointer-events-none absolute -right-8 -top-8 h-28 w-28 rounded-full bg-sky-400/10 blur-2xl"></div>
    <div class="pointer-events-none absolute -left-8 bottom-8 h-24 w-24 rounded-full bg-amber-400/10 blur-2xl"></div>

    <h2 class="text-xl font-bold text-slate-100">Týdenní přehled</h2>
    <p class="mt-1 text-xs uppercase tracking-wide text-slate-400">{{ $week['week_label'] }} • Po–Ne</p>

    @php
        $weekPercent = $week['week_possible'] > 0 ? round(($week['week_earned'] / $week['week_possible']) * 100) : 0;
    @endphp

    <div class="mt-4 rounded-2xl border border-slate-700 bg-slate-800/70 p-3">
        <div class="mb-2 flex items-center justify-between text-xs">
            <span class="text-slate-400">Celkový postup týdne</span>
            <span class="font-semibold text-amber-400">{{ $weekPercent }}%</span>
        </div>
        <div class="h-2 w-full overflow-hidden rounded-full bg-slate-900">
            <div
                class="h-full {{ $weekPercent >= 100 ? 'bg-gradient-to-r from-amber-400 via-sky-400 to-emerald-400' : 'bg-gradient-to-r from-amber-400 to-sky-400' }}"
                style="width: {{ $weekPercent }}%;"
            ></div>
        </div>
        <p class="mt-2 text-xs text-slate-400">
            <span class="text-slate-100">{{ $week['week_earned'] }}</span>
            <span class="mx-1 text-slate-500">/</span>
            <span class="text-slate-100">{{ $week['week_possible'] }}</span> XP
        </p>
    </div>

    <div class="mt-4 grid grid-cols-7 gap-2">
        @foreach($week['days'] as $day)
            <div class="text-center">
                <div class="mb-1 text-[10px] uppercase tracking-wide text-slate-500">{{ $day['weekday'] }}</div>
                <div class="relative mx-auto h-20 w-8 overflow-hidden rounded-full border border-slate-700 bg-slate-800">
                    <div
                        class="absolute inset-x-0 bottom-0 {{ $day['percent'] >= 100 ? 'bg-gradient-to-t from-amber-400 via-sky-400 to-emerald-400' : 'bg-gradient-to-t from-amber-400 to-sky-400' }}"
                        style="height: {{ $day['percent'] }}%;"
                    ></div>
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(255,255,255,0.14),transparent_50%)]"></div>
                </div>
                <div class="mt-1 text-[10px] text-slate-500">{{ $day['date_label'] }}</div>
                <div class="text-[10px] font-medium text-slate-300">{{ $day['earned'] }}/{{ $day['possible'] }}</div>
            </div>
        @endforeach
    </div>
</aside>
