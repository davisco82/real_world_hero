<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $loginField = str_contains($credentials['login'], '@') ? 'email' : 'nickname';

        if (! Auth::attempt([$loginField => $credentials['login'], 'password' => $credentials['password']])) {
            return back()->withErrors(['login' => 'Neplatné přihlašovací údaje.'])->onlyInput('login');
        }

        $request->session()->regenerate();

        return Auth::user()->role === 'parent'
            ? redirect()->route('mvp.parent')
            : redirect()->route('mvp.child');
    }

    public function showRegisterParent(): View
    {
        return view('auth.register-parent');
    }

    public function registerParent(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'parent',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('auth.child.create');
    }

    public function showRegisterChild(): View
    {
        abort_unless(Auth::check() && Auth::user()->role === 'parent', 403);

        return view('auth.register-child');
    }

    public function registerChild(Request $request): RedirectResponse
    {
        abort_unless(Auth::check() && Auth::user()->role === 'parent', 403);

        $data = $request->validate([
            'child_name' => ['required', 'string', 'max:255'],
            'nickname' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:users,nickname'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $child = Child::query()->create([
            'name' => $data['child_name'],
        ]);

        User::query()->create([
            'name' => $data['child_name'],
            'nickname' => $data['nickname'],
            'email' => $data['email'] ?? null,
            'password' => $data['password'],
            'role' => 'child',
            'parent_user_id' => Auth::id(),
            'child_id' => $child->id,
        ]);

        return redirect()->route('mvp.parent')->with('ok', 'Dítě bylo úspěšně vytvořeno.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('auth.login');
    }
}
