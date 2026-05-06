<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dětský přehled</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
    <main class="mx-auto max-w-4xl px-4 py-8">
        <header class="mb-6 flex items-center justify-between rounded-2xl border border-slate-800 bg-slate-900 p-4">
            <div class="text-sm text-slate-400">Přihlášené dítě</div>
            <form method="POST" action="{{ route('auth.logout') }}">
                @csrf
                <button type="submit" class="rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-400">Odhlásit</button>
            </form>
        </header>

        <section class="mb-6 rounded-2xl border border-slate-800 bg-slate-900 p-5">
            <h1 class="mb-3 text-2xl font-bold">{{ $child->name }} - Denní mise</h1>
            <p class="mb-3 text-slate-400">Celkové XP: <span class="text-slate-100">{{ $child->total_xp }}</span> | Úroveň: <span class="text-slate-100">{{ $child->level() }}</span></p>
            <div class="h-4 w-full overflow-hidden rounded-full bg-slate-800">
                <div class="h-full bg-amber-400" style="width: {{ $child->xpIntoCurrentLevel() }}%;"></div>
            </div>
            <p class="mt-2 text-sm text-slate-400">{{ $child->xpIntoCurrentLevel() }}/100 XP v aktuální úrovni (do další úrovně zbývá {{ $child->xpToNextLevel() }} XP)</p>
        </section>

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

        <section class="grid gap-4">
            @foreach($missions as $mission)
                @php $completion = $mission->completions->first(); @endphp
                <article class="rounded-2xl border border-slate-800 bg-slate-900 p-5">
                    <h3 class="text-lg font-semibold text-slate-100">{{ $mission->title }}</h3>
                    <p class="mt-1 text-sm text-slate-400">Doména: {{ $mission->domain->name }} | Odměna: {{ $mission->xp_reward }} XP</p>

                    <div class="mt-4">
                        @if(!$completion)
                            <form method="POST" action="{{ route('mvp.complete', $mission) }}">
                                @csrf
                                <button type="submit" class="rounded-lg bg-amber-400 px-4 py-2 font-semibold text-slate-950 hover:bg-amber-300">Označit jako splněné</button>
                            </form>
                        @elseif($completion->status === 'pending_parent')
                            <p class="font-medium text-orange-500">Čeká na potvrzení rodičem</p>
                        @elseif($completion->status === 'approved')
                            <p class="font-medium text-sky-400">Potvrzeno ✅ XP připsáno</p>
                        @endif
                    </div>
                </article>
            @endforeach
        </section>
    </main>
</body>
</html>
