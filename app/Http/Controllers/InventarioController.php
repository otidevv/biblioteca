<?php

namespace App\Http\Controllers;

use App\Jobs\GenerarReporteInventarioFisicoJob;
use App\Models\Editorial;
use App\Models\Libro;
use App\Models\Proveedor;
use App\Models\ReporteGenerado;
use App\Services\ReporteInventarioFisicoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use function dispatch;

class InventarioController extends Controller
{
    public function index(string $modulo)
    {
        return match ($modulo) {
            'compras' => $this->compras(),
            'compra_nuevo' => $this->compra_nuevo(),
            'fisico' => $this->fisico(app(ReporteInventarioFisicoService::class)),
            default => abort(404),
        };
    }

    protected function compras()
    {
        return view('inventario.compras');
    }

    protected function compra_nuevo()
    {
        $proveedores = Proveedor::latest()->get();
        $editoriales = Editorial::latest()->get();
        $libros = Libro::with(['autores', 'editorial'])->get();

        return view('inventario.compra_nuevo', compact('proveedores', 'editoriales', 'libros'));
    }

    protected function fisico(ReporteInventarioFisicoService $service)
    {
        $usuario = Auth::user();
        $contexto = $service->resolverContextoBibliotecas($usuario);
        $bibliotecas = $service->obtenerBibliotecasVisibles($usuario);
        $historialReportes = ReporteGenerado::query()
            ->where('user_id', $usuario->id)
            ->where('modulo', 'inventario_fisico')
            ->latest('id')
            ->limit(20)
            ->get();
        $reportesPendientes = $historialReportes->whereIn('estado', ['pendiente', 'procesando'])->count();
        $ultimoReporteListo = $historialReportes->firstWhere('estado', 'completado');

        return view('inventario.fisico', [
            'bibliotecas' => $bibliotecas,
            'bibliotecaFijaId' => $contexto['bibliotecaFijaId'],
            'puedeFiltrarBiblioteca' => $contexto['puedeFiltrarBiblioteca'],
            'historialReportes' => $historialReportes,
            'reportesPendientes' => $reportesPendientes,
            'ultimoReporteListo' => $ultimoReporteListo,
        ]);
    }

    public function solicitarReporteFisico(Request $request)
    {
        $data = $request->validate([
            'formato' => 'required|in:excel,pdf',
            'biblioteca_id' => 'nullable',
        ]);

        $reporte = ReporteGenerado::create([
            'user_id' => Auth::id(),
            'modulo' => 'inventario_fisico',
            'formato' => $data['formato'],
            'filtros' => [
                'biblioteca_id' => $data['biblioteca_id'] ?? null,
            ],
            'estado' => 'pendiente',
            'solicitado_en' => now(),
        ]);

        $job = new GenerarReporteInventarioFisicoJob($reporte->id);
        $this->despacharReporte($job);

        return response()->json([
            'success' => true,
            'message' => 'La solicitud del reporte fue registrada. Te aparecera en el historial cuando este lista.',
        ]);
    }

    public function descargarReporteFisico(ReporteGenerado $reporte)
    {
        abort_if($reporte->user_id !== Auth::id(), 403, 'No autorizado');
        abort_if($reporte->modulo !== 'inventario_fisico', 404);
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
}
