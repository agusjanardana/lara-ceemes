<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Login · Lara Ceemes</title><link rel="stylesheet" href="{{ asset('vendor/ceemes/ceemes.css') }}"></head>
<body>
<main class="ceemes-auth">
    <section class="ceemes-auth-card">
        <a class="ceemes-brand" href="{{ url('/') }}">Lara Ceemes</a>
        <h1>Selamat datang.</h1><p class="ceemes-muted">Masuk dengan akun superadmin untuk mengelola content website.</p>
        @if($errors->any())<div class="ceemes-alert ceemes-alert-danger"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <form method="POST" action="{{ route('ceemes.login.store') }}">@csrf
            <div class="ceemes-field"><label for="email">Email address</label><input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" placeholder="admin@example.com"></div>
            <div class="ceemes-field" style="margin-top:15px"><label for="password">Password</label><input id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••"></div>
            <label class="ceemes-check"><input type="checkbox" name="remember" value="1"> Ingat saya di perangkat ini</label>
            <button class="ceemes-button" type="submit">Masuk ke Admin <span>→</span></button>
        </form>
        <p class="ceemes-auth-help">Belum punya akun? Jalankan <code>php artisan ceemes:make-superadmin</code></p>
    </section>
</main>
</body>
</html>
