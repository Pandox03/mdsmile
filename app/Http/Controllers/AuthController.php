<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth as AuthFacade;

class AuthController extends Controller
{
    public function showLoginForm(Request $request): Response
    {
        // Ensure login form always carries a fresh CSRF token,
        // especially after idle timeout/session invalidation.
        $request->session()->regenerateToken();

        return response()
            ->view('auth.login')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, private, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Never persist login on shared clinic devices ("remember me" removed from UI).
        if (AuthFacade::attempt($credentials, false)) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => __('Les identifiants fournis sont incorrects.'),
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        AuthFacade::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
