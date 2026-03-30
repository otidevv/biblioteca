<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(Request $request): View
    {
        $redirect = $request->query('redirect');

        if ($this->isSafeLocalRedirect($redirect)) {
            $request->session()->put('auth.redirect', $redirect);
        }

        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $user = $request->user();
        $redirect = $request->input('redirect') ?: $request->session()->pull('auth.redirect');

        if ($this->isSafeLocalRedirect($redirect)) {
            return redirect()->to($redirect);
        }

        $roles = $user->usuarioRolBibliotecas()
            ->where('estado', 1)
            ->pluck('rol_id')
            ->toArray();

        if (in_array(5, $roles)) {
            return redirect()->route('home');
        }

        return redirect()->route('dashboard');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    protected function isSafeLocalRedirect(?string $redirect): bool
    {
        if (!$redirect) {
            return false;
        }

        if (str_starts_with($redirect, '/')) {
            return true;
        }

        return str_starts_with($redirect, url('/'));
    }
}
