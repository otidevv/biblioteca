<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PermisoPorRuta
{
    protected array $pathPermissionMap = [
        'administracion/usuarios' => 'administracion.usuarios',
        'api/usuarios' => 'administracion.usuarios',
        'administracion/roles_permisos' => 'administracion.roles_permisos',
        'api/roles' => 'administracion.roles_permisos',
        'administracion/bibliotecas' => 'administracion.bibliotecas',
        'api/bibliotecas' => 'administracion.bibliotecas',
        'administracion/backups' => 'administracion.backups',
        'administracion/proveedores' => 'administracion.proveedores',
        'api/proveedores' => 'administracion.proveedores',
        'administracion/editoriales' => 'administracion.editoriales',
        'api/editoriales' => 'administracion.editoriales',
        'administracion/tipo_registros' => 'administracion.tipo_registros',
        'api/tipo_registros' => 'administracion.tipo_registros',
        'administracion/autores' => 'administracion.autores',
        'api/autores' => 'administracion.autores',
        'administracion/sanciones' => 'administracion.sanciones',
        'api/sanciones' => 'administracion.sanciones',
        'administracion/notificaciones' => 'administracion.notificaciones',
        'api/notificaciones' => 'administracion.notificaciones',
        'administracion/actividades' => 'administracion.actividades',
        'api/actividades' => 'administracion.actividades',
        'administracion/libros' => 'administracion.libros',
        'administracion/libros_editar' => 'administracion.libros',
        'administracion/libros_nuevo' => 'administracion.libros',
        'administracion/ejemplares' => 'administracion.libros',
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
        //'api/inventario/actualizar' => 'administracion.libros',
        'api/administracion' => 'administracion.libros',
        'inventario/compras' => 'inventario.compras',
        'api/inventario/compras' => 'inventario.compras',
        'inventario/fisico' => 'inventario.fisico',
        'api/inventario/fisico' => 'inventario.fisico',
        'prestamos/reservas' => 'prestamos.reservas',
        'api/prestamos/reservas' => 'prestamos.reservas',
        'api/prestamos/reserva' => 'prestamos.reservas',
        'prestamos/registro' => 'prestamos.registro',
        'api/prestamos' => 'prestamos.registro',
        'lectores/registro' => 'lectores.registro',
        'api/usuarios/lectores' => 'lectores.registro',
        'lectores/historial' => 'lectores.historial',
        'lectores/importacion' => 'lectores.importacion',
        'api/externo' => 'lectores.registro',
        'reportes/grafico' => 'reportes.grafico',
        'reportes/descargas' => 'reportes.descargas',
        'sincronizar' => 'administracion',
        'clasificarLibrosMasivos' => 'administracion',
        'actualizarCodigosTopograficos' => 'administracion',
        'sincronizarCirculacion' => 'administracion',
        'obtenerDeweyPorTitulo' => 'administracion',
    ];

    protected $submoduloPermisos = [
        'usuarios.' => 'administracion.usuarios',
        'roles.' => 'administracion.roles_permisos',
        'lectores.' => 'lectores.registro',
        // Agrega más submódulos aquí
    ];

    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            abort(403, 'No autenticado');
        }

        $ruta = $request->route()?->getName();
        $permiso = null;

        // Mapear prefijo de ruta al permiso de submódulo
        if ($ruta) {
            foreach ($this->submoduloPermisos as $prefijo => $submodulo) {
                if (str_starts_with($ruta, $prefijo)) {
                    $permiso = $submodulo;
                    break;
                }
            }

            $permiso ??= $ruta;
        } else {
            $path = trim($request->path(), '/');

            foreach ($this->pathPermissionMap as $prefix => $permission) {
                if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                    $permiso = $permission;
                    break;
                }
            }
        }

        if (!$permiso) {
            abort(403, 'No autorizado');
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
