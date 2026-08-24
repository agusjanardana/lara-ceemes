<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{ config('app.name', 'Lara Ceemes') }}</title><link rel="stylesheet" href="{{ asset('vendor/ceemes/ceemes.css') }}"></head>
<body>
<main class="ceemes-home">
    <section class="ceemes-home-card">
        <a class="ceemes-brand" href="/">Lara Ceemes</a>
        <span class="ceemes-kicker" style="margin-top:45px;display:block">Content engine for Laravel</span>
        <h1>Konten terstruktur. Frontend tetap milik Anda.</h1>
        <p>Lara Ceemes sudah terpasang dan siap digunakan. Kelola Collection, Entry, Section, Media, Navigation, dan SEO melalui Admin CMS.</p>
        <div class="ceemes-actions">
            @auth<a class="ceemes-button" href="{{ route('ceemes.admin.dashboard') }}">Buka Admin CMS →</a>
            @elseif(Route::has((string) config('ceemes.auth.login_route_name', 'login')))<a class="ceemes-button" href="{{ route((string) config('ceemes.auth.login_route_name', 'login')) }}">Login Admin →</a>@endauth
        </div>
        <div class="ceemes-home-meta"><span><i></i> Package aktif</span><span>janar/lara-ceemes</span></div>
    </section>
</main>
</body>
</html>
