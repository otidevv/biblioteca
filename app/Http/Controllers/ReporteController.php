<?php

namespace App\Http\Controllers;

use App\Jobs\GenerarReporteHistorialPrestamosJob;
use App\Jobs\GenerarReporteInventarioFisicoJob;
use App\Models\Actividad;
use App\Models\Compra;
use App\Models\Ejemplar;
use App\Models\Libro;
use App\Models\Prestamo;
use App\Models\ReporteGenerado;
use App\Models\Reservacion;
use App\Models\Sancion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use function dispatch;

class ReporteController extends Controller
{
    public function grafico()
    {
        $resumen = [
            'libros' => Schema::hasTable('libros') ? Libro::count() : 0,
            'ejemplares' => Schema::hasTable('ejemplares') ? Ejemplar::count() : 0,
            'lectores' => Schema::hasTable('users')
                ? User::query()
                    ->where(function ($query) {
                        $query->where('tipo_usuario', 'lector')
                            ->orWhereHas('roles', function ($rolesQuery) {
                                $rolesQuery->where('roles.nombre', 'LECTOR')
                                    ->wherePivot('estado', 1);
                            });
                    })
                    ->distinct()
                    ->count('users.id')
                : 0,
            'prestamos_activos' => Schema::hasTable('prestamos') ? Prestamo::where('estado', 1)->count() : 0,
            'reservas_pendientes' => Schema::hasTable('reservaciones') ? Reservacion::where('estado', 0)->count() : 0,
            'compras' => Schema::hasTable('compras') ? Compra::count() : 0,
            'actividades_activas' => Schema::hasTable('actividades') ? Actividad::where('estado', 1)->count() : 0,
            'notificaciones_activas' => Schema::hasTable('notificaciones')
                ? \App\Models\Notificacion::query()
                    ->where('estado', 1)
                    ->where(function ($query) {
                        $query->whereNull('fecha_publicacion')
                            ->orWhere('fecha_publicacion', '<=', now());
                    })
                    ->where(function ($query) {
                        $query->whereNull('fecha_expiracion')
                            ->orWhere('fecha_expiracion', '>=', now());
                    })
                    ->count()
                : 0,
        ];

        $modulos = [
            [
                'titulo' => 'Circulacion y prestamos',
                'icono' => 'bi-arrow-left-right',
                'descripcion' => 'Monitorea prestamos activos, reservas pendientes y sanciones vigentes para decisiones de circulacion.',
                'metricas' => [
                    ['etiqueta' => 'Prestamos activos', 'valor' => $resumen['prestamos_activos']],
                    ['etiqueta' => 'Reservas pendientes', 'valor' => $resumen['reservas_pendientes']],
                    ['etiqueta' => 'Sanciones activas', 'valor' => Schema::hasTable('sanciones') ? Sancion::where('estado', 1)->count() : 0],
                ],
                'acciones' => [
                    ['texto' => 'Ver historial', 'url' => url('/lectores/historial')],
                    ['texto' => 'Ver reservas', 'url' => url('/prestamos/reservas')],
                ],
            ],
            [
                'titulo' => 'Inventario y coleccion',
                'icono' => 'bi-box-seam-fill',
                'descripcion' => 'Resume el fondo bibliografico, los ejemplares por estado y el avance de compras registradas.',
                'metricas' => [
                    ['etiqueta' => 'Libros', 'valor' => $resumen['libros']],
                    ['etiqueta' => 'Ejemplares', 'valor' => $resumen['ejemplares']],
                    ['etiqueta' => 'Disponibles', 'valor' => Schema::hasTable('ejemplares') ? Ejemplar::where('estado', 1)->count() : 0],
                ],
                'acciones' => [
                    ['texto' => 'Inventario fisico', 'url' => url('/inventario/fisico')],
                    ['texto' => 'Gestion de compras', 'url' => url('/inventario/compras')],
                ],
            ],
            [
                'titulo' => 'Lectores y comunidad',
                'icono' => 'bi-people-fill',
                'descripcion' => 'Observa la base de lectores, actividades activas y el alcance de mensajes institucionales.',
                'metricas' => [
                    ['etiqueta' => 'Lectores', 'valor' => $resumen['lectores']],
                    ['etiqueta' => 'Actividades activas', 'valor' => $resumen['actividades_activas']],
                    ['etiqueta' => 'Notificaciones vigentes', 'valor' => $resumen['notificaciones_activas']],
                ],
                'acciones' => [
                    ['texto' => 'Gestionar lectores', 'url' => url('/lectores/registro')],
                    ['texto' => 'Gestionar actividades', 'url' => url('/administracion/actividades')],
                ],
            ],
            [
                'titulo' => 'Centro de exportaciones',
                'icono' => 'bi-cloud-arrow-down-fill',
                'descripcion' => 'Consulta el historial de archivos generados, su estado y los modulos que ya cuentan con descarga.',
                'metricas' => [
                    ['etiqueta' => 'Solicitudes', 'valor' => Schema::hasTable('reportes_generados') ? ReporteGenerado::count() : 0],
                    ['etiqueta' => 'Completados', 'valor' => Schema::hasTable('reportes_generados') ? ReporteGenerado::where('estado', 'completado')->count() : 0],
                    ['etiqueta' => 'Pendientes', 'valor' => Schema::hasTable('reportes_generados') ? ReporteGenerado::whereIn('estado', ['pendiente', 'procesando'])->count() : 0],
                ],
                'acciones' => [
                    ['texto' => 'Ir a descargas', 'url' => route('reportes.descargas')],
                ],
            ],
        ];

        return view('reportes.grafico', compact('resumen', 'modulos'));
    }

    public function index(Request $request)
    {
        $query = ReporteGenerado::query()
            ->where('user_id', Auth::id())
            ->latest('id');

        if ($request->filled('modulo')) {
            $query->where('modulo', $request->string('modulo'));
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->string('estado'));
        }

        $reportes = $query->paginate(15)->withQueryString();

        $resumenBase = ReporteGenerado::query()->where('user_id', Auth::id());

        return view('reportes.index', [
            'reportes' => $reportes,
            'resumen' => [
                'total' => (clone $resumenBase)->count(),
                'pendientes' => (clone $resumenBase)->whereIn('estado', ['pendiente', 'procesando'])->count(),
                'completados' => (clone $resumenBase)->where('estado', 'completado')->count(),
                'fallidos' => (clone $resumenBase)->where('estado', 'fallido')->count(),
            ],
            'modulosDisponibles' => ReporteGenerado::query()
                ->where('user_id', Auth::id())
                ->select('modulo')
                ->distinct()
                ->orderBy('modulo')
                ->pluck('modulo'),
        ]);
    }

    public function descargar(ReporteGenerado $reporte)
    {
        abort_if($reporte->user_id !== Auth::id(), 403, 'No autorizado');
        abort_if($reporte->estado !== 'completado' || empty($reporte->archivo_ruta), 404, 'El archivo aun no esta disponible.');
        abort_unless(Storage::disk('local')->exists($reporte->archivo_ruta), 404, 'Archivo no encontrado.');

        return Storage::disk('local')->download(
            $reporte->archivo_ruta,
            $reporte->archivo_nombre ?: basename($reporte->archivo_ruta)
        );
    }

    public function reintentar(ReporteGenerado $reporte)
    {
        abort_if($reporte->user_id !== Auth::id(), 403, 'No autorizado');
        abort_if($reporte->estado !== 'fallido', 422, 'Solo se pueden reintentar reportes fallidos.');

        if ($reporte->archivo_ruta && Storage::disk('local')->exists($reporte->archivo_ruta)) {
            Storage::disk('local')->delete($reporte->archivo_ruta);
        }

        $reporte->update([
            'estado' => 'pendiente',
            'error' => null,
            'archivo_nombre' => null,
            'archivo_ruta' => null,
            'total_registros' => null,
            'procesado_en' => null,
            'solicitado_en' => now(),
        ]);

        $job = match ($reporte->modulo) {
            'inventario_fisico' => new GenerarReporteInventarioFisicoJob($reporte->id),
            'lectores_historial_prestamos' => new GenerarReporteHistorialPrestamosJob($reporte->id),
            default => null,
        };

        abort_if(!$job, 422, 'Este tipo de reporte todavia no admite reintento automatico.');

        $this->despacharReporte($job);

        return redirect()
            ->route('reportes.descargas', request()->only('modulo', 'estado', 'page'))
            ->with('status', 'El reporte fue enviado nuevamente a procesamiento.');
    }

    protected function despacharReporte(object $job): void
    {
        if (app()->environment('local') && config('queue.default') === 'database') {
            dispatch($job)->afterResponse();
            return;
        }

        dispatch($job);
    }
}
