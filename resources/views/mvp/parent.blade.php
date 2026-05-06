<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rodičovský přehled</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
    @include('partials.playful-bg')
    @include('partials.topbar')

    <main class="mx-auto max-w-6xl px-4 pt-24 pb-8">
        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
            <section class="lg:pr-6 lg:border-r lg:border-slate-800">
                <section class="mb-6 rounded-2xl border border-slate-800 bg-slate-900 p-5">
                    <h2 class="mb-4 text-xl font-bold">Přehled dětí</h2>
                    @if($children->isEmpty())
                        <p class="text-slate-500">Zatím nemáte přidané žádné dítě.</p>
                    @else
                        <div class="grid gap-3 md:grid-cols-2">
                            @foreach($children as $child)
                                <article class="rounded-xl border border-slate-700 bg-slate-800 p-4">
                                    <p class="font-semibold text-slate-100">{{ $child->name }}</p>
                                    <p class="text-sm text-slate-400">XP: {{ $child->total_xp }} | Úroveň: {{ $child->level() }}</p>
                                    <p class="mt-1 text-sm text-slate-400">Do další úrovně: <span class="text-amber-400">{{ $child->xpToNextLevel() }} XP</span></p>
                                    <a href="{{ route('mvp.child', ['child_id' => $child->id]) }}" class="mt-3 inline-block rounded-lg bg-sky-500 px-3 py-2 text-sm font-semibold text-white hover:bg-sky-400">
                                        Otevřít přehled úkolů
                                    </a>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </section>

                <section class="rounded-2xl border border-slate-800 bg-slate-900 p-5">
                    <h1 class="mb-5 text-2xl font-bold">Potvrzení misí rodičem</h1>

                    @forelse($pending as $item)
                        <article class="mb-4 rounded-xl border border-slate-700 bg-slate-800 p-4 last:mb-0">
                            <h3 class="text-lg font-semibold text-slate-100">{{ $item->mission->title }}</h3>
                            <p class="mt-1 text-sm text-slate-400">Dítě: {{ $item->child->name }}</p>
                            <p class="text-sm text-slate-400">Doména: {{ $item->mission->domain->name }}</p>
                            <p class="text-sm text-slate-400">XP odměna: {{ $item->mission->xp_reward }}</p>

                            <form method="POST" action="{{ route('mvp.approve', $item) }}" class="mt-4">
                                @csrf
                                <button type="submit" class="rounded-lg bg-amber-400 px-4 py-2 font-semibold text-slate-950 hover:bg-amber-300">Potvrdit misi</button>
                            </form>
                        </article>
                    @empty
                        <p class="text-slate-500">Aktuálně nejsou žádné mise ke schválení.</p>
                    @endforelse
                </section>
            </section>

            <section class="lg:pl-1">
                <h2 class="mb-6 text-center text-xl font-semibold">Týdenní přehled</h2>
                @include('mvp.partials.weekly-overview', ['week' => $week])
            </section>
        </div>
    </main>
</body>
</html>
