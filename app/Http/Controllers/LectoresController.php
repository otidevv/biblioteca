<?php

namespace App\Http\Controllers;

use App\Jobs\GenerarReporteHistorialPrestamosJob;
use App\Models\Biblioteca;
use App\Models\Carrera;
use App\Models\Permiso;
use App\Models\Prestamo;
use App\Models\ReporteGenerado;
use App\Models\Rol;
use App\Models\Sancion;
use App\Models\User;
use App\Models\Usuario_rol_biblioteca;
use App\Services\ReporteHistorialPrestamosService;
use App\Services\LectorImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use function dispatch;

class LectoresController extends Controller
{
    public function index(string $modulo)
    {
        return match ($modulo) {
            'registro' => $this->lectores(),
            'historial' => $this->historial(),
            'penalizaciones' => $this->penalizaciones(),
            'importacion' => $this->importacion(),
            default => abort(404),
        };
    }

    protected function lectores()
    {
        $carreras = Carrera::latest()->get();

        return view('lectores.registro_lectores', compact('carreras'));
    }

    protected function importacion()
    {
        $carreras = Carrera::orderBy('nombre')->get(['id', 'nombre']);
        $columnasPlantilla = app(LectorImportService::class)->templateColumns();

        return view('lectores.importacion_lectores', compact('carreras', 'columnasPlantilla'));
    }

    protected function historial()
    {
        $user = Auth::user();
        $permiso = Usuario_rol_biblioteca::where('rol_id', 19)
            ->where('user_id', $user->id)
            ->first();

        $busqueda = trim((string) request('q', ''));
        $estado = request('estado');
        $estadoPrestamo = request('estado_prestamo');
        $fechaDesde = request('fecha_desde');
        $fechaHasta = request('fecha_hasta');

        $query = Prestamo::with(['ejemplar.libro', 'ejemplar.biblioteca', 'lector', 'bibliotecario']);

        if ($permiso && $permiso->biblioteca_id) {
            $query->whereHas('ejemplar', function ($subQuery) use ($permiso) {
                $subQuery->where('biblioteca_id', $permiso->biblioteca_id);
            });
        }

        if ($busqueda !== '') {
            $query->where(function ($subQuery) use ($busqueda) {
                $subQuery->whereHas('ejemplar.libro', function ($q) use ($busqueda) {
                    $q->where('titulo', 'like', '%' . $busqueda . '%');
                })->orWhereHas('ejemplar.biblioteca', function ($q) use ($busqueda) {
                    $q->where('nombre', 'like', '%' . $busqueda . '%');
                })->orWhereHas('lector', function ($q) use ($busqueda) {
                    $q->where('name', 'like', '%' . $busqueda . '%');
                })->orWhereHas('bibliotecario', function ($q) use ($busqueda) {
                    $q->where('name', 'like', '%' . $busqueda . '%');
                })->orWhere('id', $busqueda);
            });
        }

        if ($estado !== null && $estado !== '') {
            $query->where('estado', (int) $estado);
        }

        if ($estadoPrestamo !== null && $estadoPrestamo !== '') {
            $query->where('estado_prestamo', (int) $estadoPrestamo);
        }

        if (! empty($fechaDesde)) {
            $query->whereDate('fecha_prestamo', '>=', $fechaDesde);
        }

        if (! empty($fechaHasta)) {
            $query->whereDate('fecha_prestamo', '<=', $fechaHasta);
        }

        $historial = $query
            ->latest('fecha_prestamo')
            ->latest('id')
            ->paginate(12)
            ->withQueryString();
        $historialReportes = ReporteGenerado::query()
            ->where('user_id', $user->id)
            ->where('modulo', 'lectores_historial_prestamos')
            ->latest('id')
            ->limit(20)
            ->get();
        $reportesPendientes = $historialReportes->whereIn('estado', ['pendiente', 'procesando'])->count();

        return view('lectores.historial', compact('historial', 'historialReportes', 'reportesPendientes'));
    }

    public function solicitarReporteHistorial(Request $request, ReporteHistorialPrestamosService $service)
    {
        $data = $request->validate([
            'formato' => 'required|in:excel,pdf',
            'q' => 'nullable|string',
            'estado' => 'nullable',
            'estado_prestamo' => 'nullable',
            'fecha_desde' => 'nullable|date',
            'fecha_hasta' => 'nullable|date',
        ]);

        $reporte = ReporteGenerado::create([
            'user_id' => Auth::id(),
            'modulo' => 'lectores_historial_prestamos',
            'formato' => $data['formato'],
            'filtros' => [
                'q' => $data['q'] ?? null,
                'estado' => $data['estado'] ?? null,
                'estado_prestamo' => $data['estado_prestamo'] ?? null,
                'fecha_desde' => $data['fecha_desde'] ?? null,
                'fecha_hasta' => $data['fecha_hasta'] ?? null,
            ],
            'estado' => 'pendiente',
            'solicitado_en' => now(),
        ]);

        $job = new GenerarReporteHistorialPrestamosJob($reporte->id);
        $this->despacharReporte($job);

        return response()->json([
            'success' => true,
            'message' => 'La solicitud del reporte fue registrada. Revisa el historial para descargarlo cuando este listo.',
            'filtros' => $service->describirFiltros($reporte->filtros ?? []),
        ]);
    }

    public function descargarReporteHistorial(ReporteGenerado $reporte)
    {
        abort_if($reporte->user_id !== Auth::id(), 403, 'No autorizado');
        abort_if($reporte->modulo !== 'lectores_historial_prestamos', 404);
        abort_if($reporte->estado !== 'completado' || empty($reporte->archivo_ruta), 404, 'El archivo aun no esta disponible.');
        abort_unless(Storage::disk('local')->exists($reporte->archivo_ruta), 404, 'Archivo no encontrado.');

        return Storage::disk('local')->download($reporte->archivo_ruta, $reporte->archivo_nombre ?: basename($reporte->archivo_ruta));
    }

    protected function despacharReporte(object $job): void
    {
        if (app()->environment('local') && config('queue.default') === 'database') {
            dispatch($job)->afterResponse();
            return;
        }

        dispatch($job);
    }

    protected function penalizaciones()
    {
        $busqueda = trim((string) request('q', ''));
        $estado = request('estado');
        $tipo = trim((string) request('tipo', ''));
        $fechaDesde = request('fecha_desde');
        $fechaHasta = request('fecha_hasta');

        $query = Sancion::with([
            'usuario',
            'bibliotecario',
            'prestamo.ejemplar.libro',
            'prestamo.ejemplar.biblioteca',
            'reservacion.ejemplar.libro',
            'reservacion.ejemplar.biblioteca',
        ]);

        if ($busqueda !== '') {
            $query->where(function ($subQuery) use ($busqueda) {
                $subQuery->whereHas('usuario', function ($q) use ($busqueda) {
                    $q->where('name', 'like', '%' . $busqueda . '%')
                        ->orWhere('email', 'like', '%' . $busqueda . '%');
                })->orWhereHas('bibliotecario', function ($q) use ($busqueda) {
                    $q->where('name', 'like', '%' . $busqueda . '%');
                })->orWhereHas('prestamo.ejemplar.libro', function ($q) use ($busqueda) {
                    $q->where('titulo', 'like', '%' . $busqueda . '%');
                })->orWhereHas('reservacion.ejemplar.libro', function ($q) use ($busqueda) {
                    $q->where('titulo', 'like', '%' . $busqueda . '%');
                })->orWhere('motivo', 'like', '%' . $busqueda . '%')
                    ->orWhere('tipo', 'like', '%' . $busqueda . '%')
                    ->orWhere('codigo_pago', 'like', '%' . $busqueda . '%')
                    ->orWhere('id', $busqueda);
            });
        }

        if ($estado !== null && $estado !== '') {
            $query->where('estado', (int) $estado);
        }

        if ($tipo !== '') {
            $query->where('tipo', 'like', '%' . $tipo . '%');
        }

        if (!empty($fechaDesde)) {
            $query->whereDate('fecha_inicio', '>=', $fechaDesde);
        }

        if (!empty($fechaHasta)) {
            $query->whereDate('fecha_inicio', '<=', $fechaHasta);
        }

        $penalizaciones = $query
            ->latest('fecha_inicio')
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        $resumen = [
            'total' => (clone $query)->count(),
            'activas' => (clone $query)->where('estado', 1)->count(),
            'cerradas' => (clone $query)->where('estado', 2)->count(),
        ];

        return view('lectores.penalizaciones', compact('penalizaciones', 'resumen'));
    }

    public function descargarPlantillaImportacion(LectorImportService $service): StreamedResponse
    {
        $contenido = $service->templateXlsx();

        return response()->streamDownload(function () use ($contenido) {
            echo $contenido;
        }, 'plantilla_lectores.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
