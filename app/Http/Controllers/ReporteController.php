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
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use function dispatch;

class ReporteController extends Controller
{
    public function grafico()
    {
        $sancionesActivas = Schema::hasTable('sanciones') ? Sancion::where('estado', 1)->count() : 0;

        $resumen = [
            'libros' => Schema::hasTable('libros') ? Libro::count() : 0,
            'ejemplares' => Schema::hasTable('ejemplares') ? Ejemplar::count() : 0,
            'lectores' => Schema::hasTable('users')
                ? User::query()
                    ->where(function ($query) {
                        $query->where('tipo_usuario', 'lector')
                            ->orWhereHas('roles', function ($rolesQuery) {
                                $rolesQuery->where('roles.nombre', 'LECTOR')
                                    ->where('usuario_rol_bibliotecas.estado', 1);
                            });
                    })
                    ->distinct()
                    ->count('users.id')
                : 0,
            'prestamos_activos' => Schema::hasTable('prestamos') ? Prestamo::where('estado', 1)->count() : 0,
            'reservas_pendientes' => Schema::hasTable('reservaciones') ? Reservacion::where('estado', 0)->count() : 0,
            'compras' => Schema::hasTable('compras') ? Compra::count() : 0,
            'actividades_activas' => Schema::hasTable('actividades') ? Actividad::where('estado', 1)->count() : 0,
            'sanciones_activas' => $sancionesActivas,
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
                    ['etiqueta' => 'Sanciones activas', 'valor' => $resumen['sanciones_activas']],
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

        $graficos = [
            'circulacion' => $this->buildDonutChart([
                ['label' => 'Prestamos activos', 'value' => $resumen['prestamos_activos'], 'color' => '#0f766e', 'descripcion' => 'Prestamos que siguen en circulacion.'],
                ['label' => 'Reservas pendientes', 'value' => $resumen['reservas_pendientes'], 'color' => '#f59e0b', 'descripcion' => 'Solicitudes en espera de atencion.'],
                ['label' => 'Sanciones activas', 'value' => $resumen['sanciones_activas'], 'color' => '#dc2626', 'descripcion' => 'Casos vigentes con restriccion.'],
            ]),
            'inventario' => $this->buildComparisonChart([
                ['label' => 'Disponibles', 'value' => Schema::hasTable('ejemplares') ? Ejemplar::where('estado', 1)->count() : 0, 'color' => '#059669', 'descripcion' => 'Ejemplares listos para prestamo.'],
                ['label' => 'Prestados', 'value' => Schema::hasTable('ejemplares') ? Ejemplar::where('estado', 0)->count() : 0, 'color' => '#0284c7', 'descripcion' => 'Ejemplares fuera en circulacion.'],
                ['label' => 'Reservados', 'value' => Schema::hasTable('ejemplares') ? Ejemplar::where('estado', 2)->count() : 0, 'color' => '#d97706', 'descripcion' => 'Ejemplares apartados para entrega.'],
                ['label' => 'Otros estados', 'value' => $this->countOtherInventoryStates(), 'color' => '#7c3aed', 'descripcion' => 'Registros fuera de los estados principales.'],
            ]),
            'comunidad' => $this->buildComparisonChart([
                ['label' => 'Lectores', 'value' => $resumen['lectores'], 'color' => '#2563eb', 'descripcion' => 'Usuarios identificados como lectores.'],
                ['label' => 'Actividades activas', 'value' => $resumen['actividades_activas'], 'color' => '#0d9488', 'descripcion' => 'Agenda vigente en la biblioteca.'],
                ['label' => 'Notificaciones vigentes', 'value' => $resumen['notificaciones_activas'], 'color' => '#7c3aed', 'descripcion' => 'Mensajes activos para usuarios.'],
                ['label' => 'Compras registradas', 'value' => $resumen['compras'], 'color' => '#ea580c', 'descripcion' => 'Procesos de adquisicion almacenados.'],
            ]),
            'tendencia' => $this->buildMonthlyTrendChart(),
            'bibliotecas' => $this->buildLibrariesChart(),
        ];

        return view('reportes.grafico', compact('resumen', 'modulos', 'graficos'));
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

    protected function buildDonutChart(array $items): array
    {
        $chart = $this->normalizeChartItems($items);
        $total = $chart['total'];

        if ($total <= 0) {
            $chart['style'] = 'conic-gradient(#e2e8f0 0% 100%)';

            return $chart;
        }

        $start = 0;
        $segments = [];

        foreach ($chart['items'] as $item) {
            if ($item['value'] <= 0) {
                continue;
            }

            $end = $start + (($item['value'] / $total) * 100);
            $segments[] = sprintf('%s %.4f%% %.4f%%', $item['color'], $start, $end);
            $start = $end;
        }

        $chart['style'] = 'conic-gradient(' . implode(', ', $segments ?: ['#e2e8f0 0% 100%']) . ')';

        return $chart;
    }

    protected function buildComparisonChart(array $items): array
    {
        return $this->normalizeChartItems($items);
    }

    protected function buildMonthlyTrendChart(): array
    {
        $months = collect(range(5, 0))
            ->map(fn (int $offset) => now()->startOfMonth()->subMonths($offset));

        $labels = [];
        $series = [
            ['label' => 'Prestamos', 'color' => '#0f766e', 'values' => []],
            ['label' => 'Reservas', 'color' => '#f59e0b', 'values' => []],
            ['label' => 'Compras', 'color' => '#2563eb', 'values' => []],
        ];

        foreach ($months as $month) {
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();

            $labels[] = $this->formatMonthLabel($month);
            $series[0]['values'][] = $this->countRecordsBetween('prestamos', 'fecha_prestamo', Prestamo::class, $start, $end);
            $series[1]['values'][] = $this->countRecordsBetween('reservaciones', 'fecha_reservacion', Reservacion::class, $start, $end);
            $series[2]['values'][] = $this->countRecordsBetween('compras', 'fecha_compra', Compra::class, $start, $end);
        }

        $max = collect($series)
            ->flatMap(fn (array $serie) => $serie['values'])
            ->max() ?: 0;

        return [
            'labels' => $labels,
            'series' => $series,
            'max' => $max,
            'empty' => $max === 0,
        ];
    }

    protected function buildLibrariesChart(): array
    {
        if (!Schema::hasTable('bibliotecas') || !Schema::hasTable('ejemplares')) {
            return [
                'items' => [],
                'max' => 0,
                'empty' => true,
            ];
        }

        $items = \App\Models\Biblioteca::query()
            ->withCount('ejemplares')
            ->orderByDesc('ejemplares_count')
            ->limit(5)
            ->get()
            ->map(function ($biblioteca) {
                return [
                    'label' => $biblioteca->nombre,
                    'value' => (int) $biblioteca->ejemplares_count,
                    'color' => '#0f766e',
                    'descripcion' => 'Ejemplares registrados en esta biblioteca.',
                ];
            })
            ->filter(fn (array $item) => $item['value'] > 0)
            ->values()
            ->all();

        $chart = $this->normalizeChartItems($items);
        $chart['empty'] = $chart['max'] === 0;

        return $chart;
    }

    protected function normalizeChartItems(array $items): array
    {
        $items = array_map(function (array $item) {
            return [
                'label' => $item['label'],
                'value' => (int) ($item['value'] ?? 0),
                'color' => $item['color'] ?? '#0f766e',
                'descripcion' => $item['descripcion'] ?? null,
            ];
        }, $items);

        $total = collect($items)->sum('value');
        $max = collect($items)->max('value') ?: 0;

        $items = array_map(function (array $item) use ($total, $max) {
            $item['percentage'] = $total > 0 ? round(($item['value'] / $total) * 100, 1) : 0;
            $item['relative'] = $max > 0 ? round(($item['value'] / $max) * 100, 2) : 0;

            return $item;
        }, $items);

        return [
            'items' => $items,
            'total' => $total,
            'max' => $max,
            'empty' => $total === 0,
        ];
    }

    protected function countOtherInventoryStates(): int
    {
        if (!Schema::hasTable('ejemplares')) {
            return 0;
        }

        return Ejemplar::query()
            ->whereNotIn('estado', [0, 1, 2])
            ->count();
    }

    protected function countRecordsBetween(string $table, string $column, string $modelClass, Carbon $start, Carbon $end): int
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
            return 0;
        }

        return $modelClass::query()
            ->whereBetween($column, [$start, $end])
            ->count();
    }

    protected function formatMonthLabel(Carbon $month): string
    {
        $months = [
            1 => 'Ene',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Abr',
            5 => 'May',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Ago',
            9 => 'Sep',
            10 => 'Oct',
            11 => 'Nov',
            12 => 'Dic',
        ];

        return ($months[(int) $month->format('n')] ?? $month->format('m')) . ' ' . $month->format('y');
    }
}
