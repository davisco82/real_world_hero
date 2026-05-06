<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dětský přehled</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
    @include('partials.playful-bg')
    @include('partials.topbar')

    <main class="mx-auto max-w-6xl px-4 py-8">
        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
            <section class="lg:pr-6 lg:border-r lg:border-slate-800">
                <h1 class="mb-6 text-center text-xl font-semibold">Moje dnešní mise</h1>

                @if($achievementTitles->count())
                    <section class="mb-6 rounded-2xl border border-slate-800 bg-slate-800 p-5">
                        <h2 class="mb-2 text-lg font-semibold text-slate-100">Achievementy</h2>
                        <ul class="list-disc space-y-1 pl-5 text-slate-400">
                            @foreach($achievementTitles as $title)
                                <li class="text-orange-500">{{ $title }}</li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                @php
                    $periodMap = [
                        '20 minut pohybu' => 'Ráno',
                        'Dokonči domácí úkol před večeří' => 'Odpoledne',
                        'Připrav si školní tašku' => 'Večer',
                    ];

                    $board = ['Ráno' => [], 'Odpoledne' => [], 'Večer' => []];
                    foreach ($missions as $mission) {
                        $period = $periodMap[$mission->title] ?? 'Odpoledne';
                        $board[$period][] = $mission;
                    }
                @endphp

                <section class="rounded-2xl border border-slate-800 bg-slate-900 p-4">
                    <div class="space-y-4">
                        @foreach(['Ráno', 'Odpoledne', 'Večer'] as $period)
                            @php $periodMissions = collect($board[$period])->take(3)->values(); @endphp
                            <div class="rounded-xl border border-slate-800 bg-slate-800/50 p-3">
                                <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-300">{{ $period }}</h2>

                                <div class="grid gap-3 md:grid-cols-3">
                                    @for($i = 0; $i < 3; $i++)
                                        @php $mission = $periodMissions->get($i); @endphp
                                        @if($mission)
                                            @php $completion = $mission->completions->first(); @endphp
                                            <article class="rotate-[-1deg] rounded-lg border border-amber-300/40 bg-amber-200 p-3 text-slate-950 shadow-sm">
                                                <div class="mb-2 h-1.5 w-10 rounded-full bg-orange-500/80"></div>
                                                <h3 class="text-sm font-bold">{{ $mission->title }}</h3>
                                                <p class="mt-1 text-xs text-slate-700">{{ $mission->domain->name }} • {{ $mission->xp_reward }} XP</p>

                                                <div class="mt-3">
                                                    @if(!$completion || $completion->status === 'not_completed')
                                                        <form method="POST" action="{{ route('mvp.complete', $mission) }}">
                                                            @csrf
                                                            <button type="submit" class="rounded-md bg-slate-950 px-2.5 py-1 text-xs font-semibold text-amber-300 hover:bg-slate-800">Splněno</button>
                                                        </form>
                                                    @elseif($completion->status === 'pending_parent')
                                                        <p class="text-xs font-semibold text-orange-600">Čeká na potvrzení</p>
                                                    @elseif($completion->status === 'approved')
                                                        <p class="text-xs font-semibold text-sky-700">Potvrzeno ✅</p>
                                                    @elseif($completion->status === 'rejected')
                                                        <p class="text-xs font-semibold text-orange-600">Zamítnuto</p>
                                                    @endif
                                                </div>
                                            </article>
                                        @else
                                            <div class="rounded-lg border border-slate-700/70 bg-slate-900/70 p-3">
                                                <div class="min-h-[108px] rounded-md bg-[linear-gradient(to_bottom,transparent_23px,rgba(148,163,184,0.17)_24px)] bg-[length:100%_24px]"></div>
                                            </div>
                                        @endif
                                    @endfor
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            </section>

            <section class="lg:pl-1">
                @include('mvp.partials.weekly-overview', ['week' => $week])
            </section>
        </div>
    </main>
</body>
</html>
