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

<header class="relative z-20 border-b border-slate-800 bg-slate-900/95">
    <div class="mx-auto flex h-16 w-full max-w-6xl items-center justify-between gap-4 px-4">
        <div class="flex items-center gap-4">
            <div class="relative h-16 w-44 shrink-0">
                <div class="group absolute top-0 left-0 rounded-2xl">
                    <img src="{{ route('app.logo') }}" alt="Real World Hero logo"
                         class="h-36 w-auto max-w-none transition duration-300 ease-in-out group-hover:scale-105 group-hover:-rotate-2 group-hover:drop-shadow-[0_0_18px_rgba(251,191,36,0.45)]">
                </div>
            </div>

            @if($user?->role === 'child' && $childForXp)
                <div class="rounded-full border border-sky-400/25 bg-slate-800/80 px-4 py-1.5 text-xs text-slate-300 shadow-[0_0_20px_rgba(56,189,248,0.12)]">
                    <span>Úroveň <span class="font-semibold text-slate-100">{{ $childForXp->level() }}</span></span>
                    <span class="mx-1.5 text-slate-500">|</span>
                    <span>XP <span class="font-semibold text-slate-100">{{ $childForXp->total_xp }}</span></span>
                    <span class="mx-1.5 text-slate-500">|</span>
                    <span>Zbývá <span class="font-semibold text-amber-400">{{ $childForXp->xpToNextLevel() }} XP</span></span>
                </div>
            @endif
        </div>

        <nav class="flex items-center gap-4 text-sm">
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

                <span class="text-slate-400">{{ $user->name }}</span>

                <form method="POST" action="{{ route('auth.logout') }}">
                    @csrf
                    <button type="submit" class="text-slate-100 hover:text-orange-500">Odhlášení</button>
                </form>
            @endif
        </nav>
    </div>
</header>
