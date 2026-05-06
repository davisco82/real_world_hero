@php
    use App\Models\Child;
    use App\Models\User;
    use Illuminate\Support\Facades\Auth;

    $user = Auth::user();
    $childForXp = null;

    if ($user?->role === 'child' && $user->child_id) {
        $childForXp = Child::query()->find($user->child_id);
    }

@endphp

<header class="border-b border-slate-800 bg-slate-900/95">
    <div class="mx-auto flex w-full max-w-6xl flex-wrap items-center justify-between gap-4 px-4 py-3">
        <div class="flex items-center gap-3">
            <div class="group rounded-2xl">
                <img src="{{ route('app.logo') }}" alt="Real World Hero logo"
                     class="h-32 w-auto transition duration-300 ease-in-out group-hover:scale-105 group-hover:-rotate-2 group-hover:drop-shadow-[0_0_18px_rgba(251,191,36,0.45)]">
            </div>
            <div>
                @if($user)
                    <p class="text-sm text-slate-400">{{ $user->name }}</p>
                    @if($user->role === 'child' && $childForXp)
                        <div class="mt-2 rounded-full border border-slate-700 bg-slate-800 px-3 py-1 text-xs text-slate-400">
                            <span>Úroveň: <span class="text-slate-100">{{ $childForXp->level() }}</span></span>
                            <span class="mx-1 text-slate-500">|</span>
                            <span>XP celkem: <span class="text-slate-100">{{ $childForXp->total_xp }}</span></span>
                            <span class="mx-1 text-slate-500">|</span>
                            <span>Zbývá <span class="text-amber-400">{{ $childForXp->xpToNextLevel() }} XP</span></span>
                        </div>
                    @endif
                @else
                    <p class="text-sm text-slate-500">Nepřihlášený uživatel</p>
                @endif
            </div>
        </div>

        <nav class="flex flex-wrap items-center gap-4 text-sm">
            @if(!$user)
                <a href="{{ route('login') }}" class="text-slate-100 hover:text-sky-400">Přihlášení</a>
            @else
                @if($user->role === 'child')
                    <a href="{{ route('mvp.child') }}" class="text-slate-100 hover:text-sky-400">Přehled úkolů</a>
                @endif

                @if($user->role === 'parent')
                    <a href="{{ url('/admin/mission-completions') }}" class="text-slate-100 hover:text-sky-400">Přehled úkolů</a>
                    <a href="{{ route('mvp.parent') }}" class="text-slate-100 hover:text-sky-400">Potvrzení úkolů</a>
                    <a href="{{ route('auth.child.create') }}" class="text-slate-100 hover:text-sky-400">Přidat dítě</a>
                @endif

                <form method="POST" action="{{ route('auth.logout') }}">
                    @csrf
                    <button type="submit" class="text-slate-100 hover:text-orange-500">Odhlášení</button>
                </form>
            @endif
        </nav>
    </div>
</header>
