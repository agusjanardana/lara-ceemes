<?php

declare(strict_types=1);

namespace LaraCeemes\Http\Controllers\Auth;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

final class LoginController
{
    public function create(Factory $views): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('ceemes.admin.dashboard');
        }

        return $views->make('ceemes::auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $emailAttribute = (string) config('ceemes.users.email_attribute', 'email');
        $credentials = [
            $emailAttribute => $validated['email'],
            'password' => $validated['password'],
        ];

        if (! Auth::attempt($credentials, (bool) ($validated['remember'] ?? false))) {
            return back()
                ->withErrors(['email' => 'Email atau password tidak sesuai.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();
        $gate = (string) config('ceemes.admin.gate', 'access-ceemes');

        if (Gate::denies($gate)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors(['email' => 'Akun ini bukan superadmin Lara Ceemes.'])
                ->onlyInput('email');
        }

        return redirect()->intended(route('ceemes.admin.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route((string) config('ceemes.auth.login_route_name', 'login'));
    }
}
