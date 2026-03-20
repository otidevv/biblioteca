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
    public function create(): View
    {
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

        // 1. Si viene un parámetro redirect en la URL, úsalo
        if ($request->has('redirect')) {
            return redirect()->to($request->get('redirect'));
        }

        // 2. Si no hay redirect, revisa roles
        $roles = $user->usuarioRolBibliotecas()
                    ->where('estado', 1)
                    ->pluck('rol_id')
                    ->toArray();

        if (in_array(5, $roles)) {
            // Página para lectores
            return redirect()->route('pagina.index');
        }

        // Página general para otros roles

        return redirect()->intended(route('dashboard', absolute: false));
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
}
