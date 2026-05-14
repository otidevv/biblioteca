<?php

namespace App\Http\Middleware;

use App\Models\Visita;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class RegistrarVisita
{
    public function handle(Request $request, Closure $next): Response
    {
        // Solo GET, solo páginas HTML (no AJAX ni assets)
        if ($request->isMethod('GET') && ! $request->ajax() && ! $request->expectsJson()) {
            $hoy      = now()->toDateString();
            $claveHoy = 'visita_contada_' . $hoy;

            if (! $request->session()->has($claveHoy)) {
                try {
                    DB::table('visitas')->insertOrIgnore([
                        'session_id' => $request->session()->getId(),
                        'ip'         => $request->ip(),
                        'user_id'    => auth()->id(),
                        'fecha'      => $hoy,
                        'created_at' => now(),
                    ]);
                } catch (\Throwable) {
                    // No interrumpir la petición si falla el registro
                }

                $request->session()->put($claveHoy, true);
            }
        }

        return $next($request);
    }
}
