<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PermisoPorRuta
{
    protected $submoduloPermisos = [
        'usuarios.' => 'administracion.usuarios',
        'roles.'    => 'administracion.roles_permisos',
        'lectores.' => 'lectores.registro',
        // Agrega más submódulos aquí
    ];

    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            abort(403, 'No autenticado');
        }

        $ruta = $request->route()?->getName();

        // Si la ruta no tiene nombre, no se valida
        if (!$ruta) {
            return $next($request);
        }

        // Mapear prefijo de ruta al permiso de submódulo
        $permiso = $ruta; // default
        foreach ($this->submoduloPermisos as $prefijo => $submodulo) {
            if (str_starts_with($ruta, $prefijo)) {
                $permiso = $submodulo;
                break;
            }
        }

        // Verificar permiso
        $tienePermiso = auth()->user()
            ->roles()
            ->whereHas('permisos', function ($q) use ($permiso) {
                $q->where('codigo', $permiso)
                  ->orWhereHas('hijos', function ($h) use ($permiso) {
                      $h->where('codigo', $permiso);
                  });
            })
            ->exists();

        abort_if(!$tienePermiso, 403, 'No autorizado');

        return $next($request);
    }
}
