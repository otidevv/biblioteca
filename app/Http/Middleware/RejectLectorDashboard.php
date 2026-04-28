<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RejectLectorDashboard
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->usuarioRolBibliotecas()->where('estado', 1)->where('rol_id', 5)->exists()) {
            if ($request->expectsJson()) {
                abort(403, 'No autorizado');
            }

            return redirect()->route('home');
        }

        return $next($request);
    }
}
