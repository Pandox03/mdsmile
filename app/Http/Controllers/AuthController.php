<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth as AuthFacade;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(Request $request): View
    {
        // Ensure login form always carries a fresh CSRF token,
        // especially after idle timeout/session invalidation.
        $request->session()->regenerateToken();

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        if (AuthFacade::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => __('Les identifiants fournis sont incorrects.'),
        ])->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        AuthFacade::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
