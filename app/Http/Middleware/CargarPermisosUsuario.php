<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class CargarPermisosUsuario
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {

          $permisos = auth()->user()
    ->roles()
    ->with('permisos.padre')
    ->get()
    ->pluck('permisos')
    ->flatten()
    ->unique('id')
    ->groupBy(fn ($permiso) => $permiso->padre?->id ?? $permiso->id)
    ->map(function ($grupo) {

        $padre = $grupo->first()->padre ?? $grupo->first();

        return [
            'codigo' => $padre->codigo,
            'nombre' => $padre->nombre,
            'icono'  => $padre->icono,
            'subpermisos' => $grupo->map(function ($hijo) {
                return [
                    'codigo' => $hijo->codigo,
                    'nombre' => $hijo->nombre,
                    'ruta'   => str_replace('.', '/', $hijo->codigo),
                ];
            })->values()->toArray()
        ];
    })
    ->values()
    ->toArray();

View::share('permisosUsuario', $permisos);
        }

        return $next($request);
    }
}
