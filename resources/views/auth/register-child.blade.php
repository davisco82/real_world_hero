<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrace dítěte</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
    <main class="mx-auto max-w-md px-4 py-10">
        <section class="rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-lg shadow-slate-950/40">
            <h1 class="mb-6 text-2xl font-bold text-slate-100">Registrace dítěte</h1>

            @if($errors->any())
                <div class="mb-4 rounded-lg border border-orange-500/40 bg-slate-800 px-3 py-2 text-sm text-orange-500">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('auth.child.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="mb-1 block text-sm text-slate-400">Jméno dítěte</label>
                    <input type="text" name="child_name" value="{{ old('child_name') }}" required
                           class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100 outline-none focus:border-sky-400">
                </div>
                <div>
                    <label class="mb-1 block text-sm text-slate-400">Nickname dítěte (unikátní)</label>
                    <input type="text" name="nickname" value="{{ old('nickname') }}" required
                           class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100 outline-none focus:border-sky-400">
                </div>
                <div>
                    <label class="mb-1 block text-sm text-slate-400">E-mail dítěte (volitelné)</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100 outline-none focus:border-sky-400">
                </div>
                <div>
                    <label class="mb-1 block text-sm text-slate-400">Heslo dítěte</label>
                    <input type="password" name="password" required
                           class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100 outline-none focus:border-sky-400">
                </div>
                <div>
                    <label class="mb-1 block text-sm text-slate-400">Heslo dítěte znovu</label>
                    <input type="password" name="password_confirmation" required
                           class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100 outline-none focus:border-sky-400">
                </div>
                <button type="submit" class="w-full rounded-lg bg-amber-400 px-4 py-2 font-semibold text-slate-950 hover:bg-amber-300">
                    Vytvořit dítě
                </button>
            </form>

            <p class="mt-5 text-sm text-slate-400">
                <a href="{{ route('mvp.parent') }}" class="font-medium text-sky-400 hover:text-sky-300">Zpět do rodičovského přehledu</a>
            </p>
        </section>
    </main>
</body>
</html>
