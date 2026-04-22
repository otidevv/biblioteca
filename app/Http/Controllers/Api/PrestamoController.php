<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ejemplar;
use App\Models\Prestamo;
use App\Models\Usuario_rol_biblioteca;
use App\Services\SancionAutomaticaService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Auth;

class PrestamoController extends Controller
{
    public function listar(Request $request)
    {
        if (! auth()->check()) {
            return response()->json(['error' => 'Debes iniciar sesión'], 401);
        }

        $user = Auth::user();
        [$bibliotecasAsignadas, $accesoGlobal] = $this->resolverBibliotecasUsuario($user->id);

        $query = Prestamo::with(['ejemplar.libro', 'lector'])
            ->when(! $accesoGlobal, function ($q) use ($bibliotecasAsignadas) {
                if ($bibliotecasAsignadas->isEmpty()) {
                    $q->whereRaw('1 = 0');
                    return;
                }

                $q->whereHas('ejemplar', function ($sub) use ($bibliotecasAsignadas) {
                    $sub->whereIn('biblioteca_id', $bibliotecasAsignadas->all());
                });
            })
            ->orderByRaw("
                CASE
                    WHEN estado = 2 THEN 1
                    WHEN estado = 3 THEN 1
                    ELSE 0
                END ASC
            ")
            ->orderBy('created_at', 'desc');

        return DataTables::eloquent($query)
            ->addColumn('fecha_prestamo', function ($row) {
                $fechaPrestamo = optional($row->fecha_prestamo)->format('d/m/Y H:i');

                return '<span class="loan-table__date">' . ($fechaPrestamo ?: '-') . '</span>';
            })
            ->addColumn('fecha_limite_raw', function ($row) {
                $fechaBase = Carbon::parse($row->fecha_prestamo)->addDays($row->duracion);
                $fechaLimite = $fechaBase->copy()->setTime(20, 0, 0);

                return $fechaLimite->toDateTimeString();
            })
            ->addColumn('fecha_limite', function ($row) {
                $fechaBase = Carbon::parse($row->fecha_prestamo)->addDays($row->duracion);
                $fechaLimite = $fechaBase->copy()->setTime(20, 0, 0);

                return '<span class="loan-table__date">' . $fechaLimite->format('d/m/Y H:i') . '</span>';
            })
            ->addColumn('libro', function ($row) {
                $titulo = $row->ejemplar->libro->titulo ?? 'Libro no disponible';

                return '<div class="loan-table__book" title="' . e($titulo) . '">' . e($titulo) . '</div>';
            })
            ->addColumn('ejemplar', function ($row) {
                $codigoDewey = trim((string) ($row->ejemplar->codigo_dewey ?? ''));
                $codigoAnt = trim((string) ($row->ejemplar->codigo_ant ?? ''));

                $codigo = $codigoDewey !== ''
                    ? $codigoDewey . $row->ejemplar->tipo . $row->ejemplar->codigo_interno
                    : ($codigoAnt !== '' ? $codigoAnt : '-');

                return '<span class="loan-table__code">' . e($codigo) . '</span>';
            })
            ->addColumn('lector', function ($row) {
                $lector = $row->lector->name ?? 'Lector no disponible';

                return '<div class="loan-table__reader" title="' . e($lector) . '">' . e($lector) . '</div>';
            })
            ->addColumn('estado', function ($row) {
                $now = Carbon::now();
                $fechaBase = Carbon::parse($row->fecha_prestamo)->addDays($row->duracion);
                $fechaLimite = $fechaBase->copy()->setTime(20, 0, 0);
                $diff = $now->diffInSeconds($fechaLimite, false);

                if ($now->greaterThan($fechaLimite) && (int) $row->estado === 1) {
                    return '<span class="loan-pill loan-pill--danger">FUERA DE PLAZO</span>';
                }

                if ((int) $row->estado === 1) {
                    $clase = $diff < 86400 ? 'loan-countdown is-danger' : 'loan-countdown is-ok';

                    return '<span class="countdown ' . $clase . '" data-seconds="' . $diff . '"></span>';
                }

                if ((int) $row->estado === 2) {
                    return '<span class="loan-pill loan-pill--success">FINALIZADO</span>';
                }

                return '<span class="loan-pill loan-pill--neutral">--</span>';
            })
            ->addColumn('estado_prestamo', function ($row) {
                return match ((int) $row->estado_prestamo) {
                    0 => '<span class="loan-pill loan-pill--warning">PRESTADO</span>',
                    1 => '<span class="loan-pill loan-pill--success">DEVUELTO</span>',
                    2 => '<span class="loan-pill loan-pill--danger">TARDANZA</span>',
                    3 => '<span class="loan-pill loan-pill--dark">DETERIORO</span>',
                    default => '<span class="loan-pill loan-pill--neutral">--</span>',
                };
            })
            ->addColumn('prestamo_lugar', function ($row) {
                return (int) ($row->prestamo_lugar ?? $row->prestamo ?? 0) === 1
                    ? '<span class="loan-pill loan-pill--info">A CASA</span>'
                    : '<span class="loan-pill loan-pill--info">EN SALA</span>';
            })
            ->addColumn('acciones', function ($row) {
                return (int) $row->estado === 1
                    ? '<button class="btn btn-sm btn-success devolverPrestamo" data-id="' . $row->id . '">
                        <i class="fas fa-check"></i> Devolver
                    </button>'
                    : '';
            })
            ->rawColumns(['acciones', 'fecha_prestamo', 'fecha_limite', 'libro', 'ejemplar', 'lector', 'estado', 'estado_prestamo', 'prestamo_lugar'])
            ->toJson();
    }

    public function nuevoPrestamo(Request $request)
    {
        if (! auth()->check()) {
            return response()->json(['error' => 'Debes iniciar sesión'], 401);
        }

        $request->validate([
            'libro_id' => 'required',
            'lector_id' => 'required',
            'prestamo_lugar' => 'required',
            'duracion' => 'required|integer|min:1',
        ]);

        $activos = Prestamo::where('lector_id', $request->lector_id)
            ->where('estado', 'prestado')
            ->count();

        if ($activos >= 3) {
            return response()->json([
                'error' => 'Ya tienes 3 préstamos activos',
            ]);
        }

        $ejemplar = Ejemplar::where('libro_id', $request->libro_id)
            ->where('estado', '1')
            ->first();

        if (! $ejemplar) {
            return response()->json([
                'error' => 'No hay ejemplares disponibles',
            ]);
        }

        $fechaPrestamo = now();
        $fechaLimite = now()->addDays($request->duracion);

        Prestamo::create([
            'lector_id' => $request->lector_id,
            'user_id' => auth()->id(),
            'prestamo_lugar' => $request->prestamo_lugar,
            'duracion' => $request->duracion,
            'fecha_prestamo' => $fechaPrestamo,
            'fecha_limite' => $fechaLimite,
            'observaciones' => null,
            'estado' => 'prestado',
        ]);

        $ejemplar->update([
            'estado' => '0',
        ]);

        return response()->json([
            'ok' => 'Prestamo registrado correctamente',
        ]);
    }

    public function devolver(Request $request, $id)
    {
        if (! auth()->check()) {
            return response()->json(['error' => 'Debes iniciar sesión'], 401);
        }

        $user = Auth::user();
        [$bibliotecasAsignadas, $accesoGlobal] = $this->resolverBibliotecasUsuario($user->id);

        $prestamo = Prestamo::with('ejemplar')->find($id);

        if (! $prestamo) {
            return response()->json(['error' => 'No se encontró el préstamo']);
        }

        if (! $accesoGlobal && ! $bibliotecasAsignadas->contains((int) optional($prestamo->ejemplar)->biblioteca_id)) {
            return response()->json(['error' => 'No puedes devolver prestamos de otra biblioteca'], 403);
        }

        $now = Carbon::now();
        $fechaLimite = Carbon::parse($prestamo->fecha_prestamo)
            ->addDays($prestamo->duracion)
            ->setTime(20, 0, 0);

        $estadoLibro = (int) ($request->estado_libro ?? 1);
        $estadoPrestamo = $estadoLibro !== 1
            ? 3
            : ($now->greaterThan($fechaLimite) ? 2 : 1);

        $prestamo->update([
            'fecha_devolucion' => $now,
            'observaciones_devolucion' => $request->observaciones,
            'estado' => 2,
            'estado_prestamo' => $estadoPrestamo,
            'estado_libro' => $estadoLibro,
        ]);

        $prestamo->ejemplar->update([
            'estado' => 1,
        ]);

        app(SancionAutomaticaService::class)->registrarPorPrestamo($prestamo->fresh());

        return response()->json([
            'ok' => 'Devolucion registrada correctamente',
        ]);
    }

    public function previewSancion(Request $request, $id)
    {
        if (! auth()->check()) {
            return response()->json(['error' => 'Debes iniciar sesión'], 401);
        }

        $prestamo = Prestamo::find($id);

        if (! $prestamo) {
            return response()->json(['error' => 'No se encontró el préstamo'], 404);
        }

        $estadoLibro = (int) $request->input('estado_libro', 1);
        $diasRetraso = $request->filled('dias_retraso') ? max(0, (int) $request->input('dias_retraso')) : null;

        return response()->json(
            app(SancionAutomaticaService::class)->previsualizarPorPrestamo($prestamo, $estadoLibro, $diasRetraso)
        );
    }

    private function resolverBibliotecasUsuario(int $userId): array
    {
        $asignaciones = Usuario_rol_biblioteca::query()
            ->where('user_id', $userId)
            ->get(['biblioteca_id', 'estado']);

        $bibliotecasAsignadas = $asignaciones
            ->pluck('biblioteca_id')
            ->filter(fn ($id) => ! is_null($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $accesoGlobal = $bibliotecasAsignadas->isEmpty()
            && $asignaciones->contains(fn ($asignacion) => is_null($asignacion->biblioteca_id) && (int) $asignacion->estado === 1);

        return [$bibliotecasAsignadas, $accesoGlobal];
    }
}
