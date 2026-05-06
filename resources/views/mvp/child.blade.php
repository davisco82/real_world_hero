<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dětský přehled</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2rem; background: #f7fafc; color: #1a202c; }
        .card { background: white; border-radius: 10px; padding: 1rem 1.25rem; margin-bottom: 1rem; border: 1px solid #e2e8f0; }
        .progress-wrap { background: #e2e8f0; border-radius: 999px; height: 16px; overflow: hidden; }
        .progress { background: #22c55e; height: 100%; }
        button { background: #2563eb; color: white; border: none; padding: .5rem .9rem; border-radius: 8px; cursor: pointer; }
        .status { font-size: .9rem; color: #4b5563; }
        .approved { color: #16a34a; font-weight: 600; }
        .pending { color: #d97706; font-weight: 600; }
        .nav a { margin-right: 1rem; }
    </style>
</head>
<body>
    <div class="nav">
        <a href="{{ url('/child') }}">Dítě</a>
        <a href="{{ url('/parent') }}">Rodič</a>
    </div>

    <h1>{{ $child->name }} - Denní mise</h1>
    <div class="card">
        <p><strong>Celkové XP:</strong> {{ $child->total_xp }} | <strong>Úroveň:</strong> {{ $child->level() }}</p>
        <div class="progress-wrap">
            <div class="progress" style="width: {{ $child->xpIntoCurrentLevel() }}%;"></div>
        </div>
        <p class="status">{{ $child->xpIntoCurrentLevel() }}/100 XP v aktuální úrovni (do další úrovně zbývá {{ $child->xpToNextLevel() }} XP)</p>
    </div>

    @if($achievementTitles->count())
        <div class="card">
            <strong>Achievementy:</strong>
            <ul>
                @foreach($achievementTitles as $title)
                    <li>{{ $title }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @foreach($missions as $mission)
        @php $completion = $mission->completions->first(); @endphp
        <div class="card">
            <h3>{{ $mission->title }}</h3>
            <p class="status">Doména: {{ $mission->domain->name }} | Odměna: {{ $mission->xp_reward }} XP</p>

            @if(!$completion)
                <form method="POST" action="{{ route('mvp.complete', $mission) }}">
                    @csrf
                    <button type="submit">Označit jako splněné</button>
                </form>
            @elseif($completion->status === 'pending_parent')
                <p class="pending">Čeká na potvrzení rodičem</p>
            @elseif($completion->status === 'approved')
                <p class="approved">Potvrzeno ✅ XP připsáno</p>
            @endif
        </div>
    @endforeach
</body>
</html>
