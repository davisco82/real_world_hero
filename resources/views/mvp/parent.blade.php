<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rodičovský přehled</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
    <main class="mx-auto max-w-4xl px-4 py-8">
        <header class="mb-6 flex flex-wrap items-center gap-3 rounded-2xl border border-slate-800 bg-slate-900 p-4">
            <a href="{{ route('auth.child.create') }}" class="rounded-lg bg-sky-500 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-400">Přidat dítě</a>
            <form method="POST" action="{{ route('auth.logout') }}">
                @csrf
                <button type="submit" class="rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-400">Odhlásit</button>
            </form>
        </header>

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
    </main>
</body>
</html>
