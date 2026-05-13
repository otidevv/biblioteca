<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PermisoPorRuta
{
    protected array $routePermissionMap = [
        'usuarios.buscar.dni' => ['lectores.registro', 'administracion.usuarios'],
    ];

    protected array $pathPermissionMap = [
        'administracion/usuarios' => 'administracion.usuarios',
        'administracion/roles_permisos' => 'administracion.roles_permisos',
        'administracion/bibliotecas' => 'administracion.bibliotecas',
        'administracion/backups' => 'administracion.backups',
        'administracion/proveedores' => 'administracion.proveedores',
        'administracion/editoriales' => 'administracion.editoriales',
        'administracion/tipo_registros' => 'administracion.tipo_registros',
        'administracion/autores' => 'administracion.autores',
        'administracion/sanciones' => 'administracion.sanciones',
        'administracion/notificaciones' => 'administracion.notificaciones',
        'administracion/actividades' => 'administracion.actividades',
        'administracion/libros' => 'administracion.libros',
        'administracion/libros/traslados' => 'administracion.libros',
        'administracion/traslados_ejemplares' => 'administracion.libros',
        'administracion/libros_editar' => 'administracion.libros',
        'administracion/libros_nuevo' => 'administracion.libros',
        'administracion/ejemplares' => 'administracion.libros',
        'inventario/compras' => 'inventario.compras',
        'inventario/fisico' => 'inventario.fisico',
        'prestamos/reservas' => 'prestamos.reservas',
        'prestamos/registro' => 'prestamos.registro',
        'prestamos/multas*' => 'prestamos.multas',
        'lectores/registro' => 'lectores.registro',
        'lectores/historial' => 'lectores.historial',
        'lectores/penalizaciones' => 'lectores.penalizaciones',
        'lectores/importacion' => 'lectores.importacion',
        'reportes/grafico' => 'reportes.grafico',
        'reportes/descargas' => 'reportes.descargas',
        'sincronizar' => 'administracion.libros',
        'sincronizarImagenesLibrosPorIsbn' => 'administracion.libros',
        'clasificarLibrosMasivos' => 'administracion.libros',
        'actualizarCodigosTopograficos' => 'administracion.libros',
        'sincronizarCirculacion' => 'administracion.libros',
        'obtenerDeweyPorTitulo' => 'administracion.libros',

        'api/roles' => 'administracion.roles_permisos',
        'api/bibliotecas' => 'administracion.bibliotecas',
        'api/proveedores' => 'administracion.proveedores',
        'api/tipo_registros' => 'administracion.tipo_registros',
        'api/sanciones' => 'administracion.sanciones',
        'api/autores' => 'administracion.autores',
        'api/editoriales' => 'administracion.editoriales',
        'api/notificaciones' => 'administracion.notificaciones',
        'api/actividades' => 'administracion.actividades',
        'api/usuarios' => 'administracion.usuarios',
        'api/inventario/libros/listar' => 'administracion.libros',
        'api/inventario/autores' => 'administracion.libros',
        'api/inventario/editoriales' => 'administracion.libros',
        'api/inventario/materias' => 'administracion.libros',
        'api/inventario/dewey/buscar' => 'administracion.libros',
        'api/inventario/libros/check_codigo' => 'administracion.libros',
        'api/inventario/libros/sugerir-dewey' => 'administracion.libros',
        'api/inventario/libros/generar-codigo' => 'administracion.libros',
        'api/inventario/libros' => 'administracion.libros',
        'api/inventario/actualizar' => 'administracion.libros',
        'api/inventario/listar' => 'administracion.libros',
        'api/inventario/ejemplares/guardar' => 'administracion.libros',
        'api/inventario/ejemplares/actualizar' => 'administracion.libros',
        'api/inventario/ejemplares/enviar-biblioteca' => 'administracion.libros',
        'api/inventario/ejemplares/resolver-traslado' => 'administracion.libros',
        'api/inventario/ejemplares/movimientos/listar' => 'administracion.libros',
        'api/inventario/ejemplares/traslados' => 'administracion.libros',
        'api/administracion' => 'administracion.libros',
        'api/inventario/fisico' => 'inventario.fisico',

        'api/externo/buscar-dni' => ['lectores.registro', 'administracion.usuarios'],
        'api/externo' => 'lectores.registro',

        'api/usuarios/lectores' => 'lectores.registro',
        'api/prestamos/multas*' => 'prestamos.multas',
        'api/prestamos/reservas*' => 'prestamos.reservas',
        'api/prestamos/reserva*' => 'prestamos.reservas',
        'api/prestamos*' => 'prestamos.registro',
        'api/inventario/compras' => 'inventario.compras',
        'api/sincronizarImagenesLibrosPorIsbn' => 'administracion',
    ];

    protected $submoduloPermisos = [
        'administracion.libros.' => 'administracion.libros',
        'usuarios.' => 'administracion.usuarios',
        'roles.' => 'administracion.roles_permisos',
        'lectores.' => 'lectores.registro',
        'prestamos.multas.' => 'prestamos.multas',
        'prestamos.reservas.' => 'prestamos.reservas',
        'prestamos.registro.' => 'prestamos.registro',
    ];

    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            abort(403, 'No autenticado');
        }

        $ruta = $request->route()?->getName();
        $path = trim($request->path(), '/');
        $permiso = null;

        if ($ruta) {
            $permiso = $this->routePermissionMap[$ruta] ?? null;

            foreach ($this->submoduloPermisos as $prefijo => $submodulo) {
                if (!$permiso && str_starts_with($ruta, $prefijo)) {
                    $permiso = $submodulo;
                    break;
                }
            }

            if (!$permiso && isset($this->pathPermissionMap[$ruta])) {
                $permiso = $this->pathPermissionMap[$ruta];
            }

            if (!$permiso) {
                foreach ($this->pathPermissionMap as $pattern => $permission) {
                    if (Str::is($pattern, $path) || Str::is($pattern . '/*', $path)) {
                        $permiso = $permission;
                        break;
                    }
                }
            }

            $permiso ??= $ruta;
        } else {
            foreach ($this->pathPermissionMap as $pattern => $permission) {
                if (Str::is($pattern, $path) || Str::is($pattern . '/*', $path)) {
                    $permiso = $permission;
                    break;
                }
            }
        }

        if (!$permiso) {
            abort(403, 'No autorizado');
        }

        $permisos = is_array($permiso) ? $permiso : [$permiso];

        $tienePermiso = auth()->user()
            ->roles()
            ->whereHas('permisos', function ($q) use ($permisos) {
                $q->whereIn('codigo', $permisos)
                  ->orWhereHas('hijos', function ($h) use ($permisos) {
                      $h->whereIn('codigo', $permisos);
                  });
            })
            ->exists();

        abort_if(!$tienePermiso, 403, 'No autorizado');

        return $next($request);
    }
}
