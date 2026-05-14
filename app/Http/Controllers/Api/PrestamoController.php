<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Biblioteca;
use App\Models\Ejemplar;
use App\Models\Prestamo;
use App\Models\Sancion;
use App\Models\Usuario_rol_biblioteca;
use App\Services\SancionAutomaticaService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $query = Prestamo::with(['ejemplar.libro', 'ejemplar.biblioteca', 'lector.persona'])
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
            ->addColumn('fechas', function ($row) {
                $fp = optional($row->fecha_prestamo)->format('d/m/Y');
                $fechaBase = Carbon::parse($row->fecha_prestamo)->addDays($row->duracion);
                $fl = $fechaBase->copy()->setTime(20, 0, 0)->format('d/m/Y');

                return '<div class="loan-dates">' .
                    '<span class="loan-dates__row"><i class="bi bi-calendar-event me-1"></i>' . ($fp ?: '-') . '</span>' .
                    '<span class="loan-dates__row loan-dates__row--limit"><i class="bi bi-calendar-check me-1"></i>' . $fl . '</span>' .
                    '</div>';
            })
            ->addColumn('libro', function ($row) {
                $titulo = $row->ejemplar->libro->titulo ?? 'Libro no disponible';

                return '<div class="loan-table__book" title="' . e($titulo) . '">' . e($titulo) . '</div>';
            })
            ->addColumn('ejemplar', function ($row) {
                $codigoDewey = trim((string) ($row->ejemplar->codigo_dewey ?? ''));
                $codigoAnt = trim((string) ($row->ejemplar->codigo_ant ?? ''));

                $codigo = $codigoDewey !== ''
                    ? $codigoDewey . ($row->ejemplar->tipo ?? '') . $row->ejemplar->codigo_interno
                    : ($codigoAnt !== '' ? $codigoAnt : '-');

                $biblioteca = $row->ejemplar->biblioteca->nombre ?? '';

                return '<span class="loan-table__code">' . e($codigo) . '</span>' .
                    ($biblioteca
                        ? '<small class="loan-table__bib"><i class="bi bi-building me-1"></i>' . e($biblioteca) . '</small>'
                        : '');
            })
            ->addColumn('lector', function ($row) {
                $user    = $row->lector;
                $persona = optional($user)->persona;

                if ($persona && ($persona->apellido_paterno || $persona->nombres)) {
                    $nombre = trim(
                        trim($persona->apellido_paterno . ' ' . $persona->apellido_materno) .
                        ', ' . $persona->nombres
                    );
                } else {
                    $nombre = $user->name ?? 'Lector no disponible';
                }

                $dni    = $persona->dni ?? '';
                $codigo = $persona->codigo_institucional ?? '';

                $badges = '';
                if ($dni) {
                    $badges .= '<span class="loan-lector-badge">DNI ' . e($dni) . '</span>';
                }
                if ($codigo) {
                    $badges .= '<span class="loan-lector-badge loan-lector-badge--code">Cód. ' . e($codigo) . '</span>';
                }

                return '<div class="loan-table__reader" title="' . e($nombre) . '">' . e($nombre) . '</div>' .
                    ($badges
                        ? '<div class="loan-lector-badges">' . $badges . '</div>'
                        : ($user && $user->email ? '<small class="loan-table__reader-sub">' . e($user->email) . '</small>' : ''));
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
                $estadoHtml = match ((int) $row->estado_prestamo) {
                    0 => '<span class="loan-pill loan-pill--warning">PRESTADO</span>',
                    1 => '<span class="loan-pill loan-pill--success">DEVUELTO</span>',
                    2 => '<span class="loan-pill loan-pill--danger">TARDANZA</span>',
                    3 => '<span class="loan-pill loan-pill--dark">DETERIORO</span>',
                    default => '<span class="loan-pill loan-pill--neutral">--</span>',
                };

                $lugarHtml = (int) ($row->prestamo_lugar ?? 0) === 1
                    ? '<span class="loan-pill loan-pill--info loan-pill--sm">A CASA</span>'
                    : '<span class="loan-pill loan-pill--info loan-pill--sm">EN SALA</span>';

                return '<div class="loan-status-stack">' . $estadoHtml . $lugarHtml . '</div>';
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
            ->filterColumn('lector', function ($query, $keyword) {
                $query->whereHas('lector', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                      ->orWhere('email', 'like', "%{$keyword}%")
                      ->orWhereHas('persona', function ($p) use ($keyword) {
                          $p->where('dni', 'like', "%{$keyword}%")
                            ->orWhere('codigo_institucional', 'like', "%{$keyword}%")
                            ->orWhere('nombres', 'like', "%{$keyword}%")
                            ->orWhere('apellido_paterno', 'like', "%{$keyword}%")
                            ->orWhere('apellido_materno', 'like', "%{$keyword}%");
                      });
                });
            })
            ->filterColumn('libro', function ($query, $keyword) {
                $query->whereHas('ejemplar.libro', function ($q) use ($keyword) {
                    $q->where('titulo', 'like', "%{$keyword}%")
                      ->orWhere('isbn', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('ejemplar', function ($query, $keyword) {
                $query->whereHas('ejemplar', function ($q) use ($keyword) {
                    $q->where('codigo_ant', 'like', "%{$keyword}%")
                      ->orWhere('codigo_dewey', 'like', "%{$keyword}%")
                      ->orWhere('codigo_interno', 'like', "%{$keyword}%");
                });
            })
            ->rawColumns(['acciones', 'fechas', 'fecha_prestamo', 'fecha_limite', 'libro', 'ejemplar', 'lector', 'estado', 'estado_prestamo', 'prestamo_lugar'])
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

    public function biblioteacasAccesibles()
    {
        if (! auth()->check()) {
            return response()->json(['error' => 'Debes iniciar sesión'], 401);
        }

        [$bibliotecasAsignadas, $accesoGlobal] = $this->resolverBibliotecasUsuario(auth()->id());

        $query = Biblioteca::orderBy('nombre');

        if (! $accesoGlobal) {
            if ($bibliotecasAsignadas->isEmpty()) {
                return response()->json([]);
            }
            $query->whereIn('id', $bibliotecasAsignadas->all());
        }

        return response()->json($query->get(['id', 'nombre']));
    }

    public function buscarEjemplaresDisponibles(Request $request)
    {
        if (! auth()->check()) {
            return response()->json(['error' => 'Debes iniciar sesión'], 401);
        }

        $q = trim((string) $request->input('q', ''));
        $user = auth()->user();
        [$bibliotecasAsignadas, $accesoGlobal] = $this->resolverBibliotecasUsuario($user->id);

        $bibFiltro = $request->input('biblioteca_id');

        $query = Ejemplar::with(['libro', 'biblioteca'])
            ->where('estado', 1)
            ->when(! $accesoGlobal, function ($q) use ($bibliotecasAsignadas) {
                if ($bibliotecasAsignadas->isEmpty()) {
                    $q->whereRaw('1 = 0');
                    return;
                }
                $q->whereIn('biblioteca_id', $bibliotecasAsignadas->all());
            })
            ->when($bibFiltro, fn ($q) => $q->where('biblioteca_id', $bibFiltro))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->whereHas('libro', fn ($l) => $l->where('titulo', 'like', "%{$q}%"))
                        ->orWhere('codigo_ant', 'like', "%{$q}%")
                        ->orWhere('codigo_dewey', 'like', "%{$q}%");
                });
            })
            ->limit(20)
            ->get();

        return response()->json($query->map(function ($e) {
            $codigo = $e->codigo_dewey
                ? $e->codigo_dewey . ($e->tipo ?? '') . $e->codigo_interno
                : ($e->codigo_ant ?? '-');

            return [
                'id'         => $e->id,
                'libro'      => $e->libro->titulo ?? '—',
                'codigo'     => $codigo,
                'biblioteca' => $e->biblioteca->nombre ?? '—',
            ];
        }));
    }

    public function prestamoDirecto(Request $request)
    {
        if (! auth()->check()) {
            return response()->json(['error' => 'Debes iniciar sesión'], 401);
        }

        $request->validate([
            'lector_id'      => 'required|exists:users,id',
            'ejemplar_id'    => 'required|exists:ejemplares,id',
            'prestamo_lugar' => 'required|integer|in:0,1',
            'dias'           => 'required|integer|min:1',
            'observaciones'  => 'nullable|string',
        ]);

        $resultado = DB::transaction(function () use ($request) {
            $user = auth()->user();
            [$bibliotecasAsignadas, $accesoGlobal] = $this->resolverBibliotecasUsuario($user->id);

            $ejemplar = Ejemplar::lockForUpdate()->find($request->ejemplar_id);

            if (! $ejemplar || (int) $ejemplar->estado !== 1) {
                return ['status' => 422, 'payload' => ['error' => 'El ejemplar ya no está disponible']];
            }

            if (! $accesoGlobal && ! $bibliotecasAsignadas->contains((int) $ejemplar->biblioteca_id)) {
                return ['status' => 403, 'payload' => ['error' => 'No tienes acceso a este ejemplar']];
            }

            $tieneSancion = Sancion::where('user_id', $request->lector_id)
                ->where('estado', 1)
                ->whereDate('fecha_fin', '>=', now()->toDateString())
                ->exists();

            if ($tieneSancion) {
                return ['status' => 422, 'payload' => ['error' => 'El lector tiene una sanción vigente y no puede recibir préstamos']];
            }

            $prestamo = new Prestamo;
            $prestamo->lector_id      = $request->lector_id;
            $prestamo->prestamo_lugar = $request->prestamo_lugar;
            $prestamo->duracion       = $request->dias;
            $prestamo->fecha_prestamo = now();
            $prestamo->fecha_limite   = now()->addDays($request->dias);
            $prestamo->observaciones_prestamo = $request->observaciones;
            $prestamo->ejemplar_id    = $request->ejemplar_id;
            $prestamo->estado         = 1;
            $prestamo->estado_prestamo = 0;
            $prestamo->user_id        = $user->id;
            $prestamo->save();

            $ejemplar->estado = 0;
            $ejemplar->save();

            return ['status' => 200, 'payload' => ['success' => 'Préstamo registrado correctamente']];
        });

        return response()->json($resultado['payload'], $resultado['status']);
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
