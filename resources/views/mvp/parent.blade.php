<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rodičovský přehled</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2rem; background: #f7fafc; color: #1a202c; }
        .card { background: white; border-radius: 10px; padding: 1rem 1.25rem; margin-bottom: 1rem; border: 1px solid #e2e8f0; }
        button { background: #16a34a; color: white; border: none; padding: .5rem .9rem; border-radius: 8px; cursor: pointer; }
        .nav a { margin-right: 1rem; }
    </style>
</head>
<body>
    <div class="nav">
        <a href="{{ url('/child') }}">Dítě</a>
        <a href="{{ url('/parent') }}">Rodič</a>
    </div>

    <h1>Potvrzení misí rodičem</h1>

    @forelse($pending as $item)
        <div class="card">
            <h3>{{ $item->mission->title }}</h3>
            <p>Dítě: {{ $item->child->name }}</p>
            <p>Doména: {{ $item->mission->domain->name }}</p>
            <p>XP odměna: {{ $item->mission->xp_reward }}</p>
            <form method="POST" action="{{ route('mvp.approve', $item) }}">
                @csrf
                <button type="submit">Potvrdit misi</button>
            </form>
        </div>
    @empty
        <p>Aktuálně nejsou žádné mise ke schválení.</p>
    @endforelse
</body>
</html>
