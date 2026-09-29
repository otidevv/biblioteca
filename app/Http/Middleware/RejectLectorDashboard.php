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

        // El panel es solo para el personal; lectores (o usuarios sin rol activo) van a su panel de lector.
        if ($user && ! $user->esPersonal()) {
            if ($request->expectsJson()) {
                abort(403, 'No autorizado');
            }

            return redirect()->route('lector.panel');
        }

        return $next($request);
    }
}
