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
                ->with([
                    'permisos' => function ($q) {
                        $q->whereNull('permisos.permiso_id')
                          ->with('hijos');
                    }
                ])
                ->get()
                ->pluck('permisos')
                ->flatten()
                ->unique('id')
                ->map(function ($permiso) {
                    return [
                        'codigo' => $permiso->codigo,
                        'nombre' => $permiso->nombre,
                        'icono'  => $permiso->icono,
                        'subpermisos' => $permiso->hijos->map(function ($hijo) {
                        return [
                            'codigo' => $hijo->codigo,
                            'nombre' => $hijo->nombre,
                            'ruta'   => str_replace('.', '/', $hijo->codigo),
                        ];
                    })->toArray()
                    ];
                })
                ->values()
                ->toArray();

            View::share('permisosUsuario', $permisos);
        }

        return $next($request);
    }
}
