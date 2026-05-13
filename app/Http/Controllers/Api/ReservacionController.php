<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ejemplar;
use App\Models\Prestamo;
use App\Models\Reservacion;
use App\Models\Sancion;
use App\Models\Usuario_rol_biblioteca;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ReservacionController extends Controller
{
    public function listar(Request $request)
    {
        if (! auth()->check()) {
            return response()->json(['error' => 'Debes iniciar sesión'], 401);
        }

        $user = auth()->user();
        [$bibliotecasAsignadas, $accesoGlobal] = $this->resolverBibliotecasUsuario($user->id);

        $query = Reservacion::with(['ejemplar.libro.autores', 'lector'])
            ->where('estado', 0)
            ->whereRaw("TIMESTAMP(fecha_limite, '20:00:00') > ?", [now()->format('Y-m-d H:i:s')])
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
            ->addColumn('fecha', function ($row) {
                return '<div class="rsv-date-cell"><i class="bi bi-calendar3"></i><span>'
                    . $row->created_at->format('d/m/Y') . '</span></div>';
            })
            ->addColumn('fecha_limite', function ($row) {
                $now = Carbon::now();
                $fechaLimite = $row->fecha_limite_real;

                $diff = $now->diffInSeconds($fechaLimite, false);

                if ($diff <= 0) {
                    return '<span class="reservation-pill reservation-pill--danger"><i class="bi bi-x-circle-fill"></i> VENCIDO</span>';
                }

                return '<span class="countdown reservation-countdown" data-seconds="'.$diff.'"></span>';
            })
            ->addColumn('libro', function ($row) {
                $titulo = $row->ejemplar->libro->titulo ?? 'Libro no disponible';

                return '<div class="rsv-book-cell">
                    <div class="rsv-book-icon"><i class="bi bi-book-half"></i></div>
                    <span class="rsv-book-title" title="' . e($titulo) . '">' . e($titulo) . '</span>
                </div>';
            })
            ->addColumn('ejemplar', function ($row) {
                $codigo = $row->ejemplar->codigo_dewey
                    ? $row->ejemplar->codigo_dewey.$row->ejemplar->tipo.$row->ejemplar->codigo_interno
                    : $row->ejemplar->codigo_ant;

                return '<span class="rsv-code-badge"><i class="bi bi-upc"></i> ' . e($codigo ?: '-') . '</span>';
            })
            ->addColumn('lector', function ($row) {
                $nombre  = $row->lector->name ?? 'Lector no disponible';
                $inicial = mb_strtoupper(mb_substr($nombre, 0, 1));
                $palette = ['#7c3aed','#dc2626','#2563eb','#16a34a','#0891b2','#d97706'];
                $color   = $palette[ord($inicial) % count($palette)];

                return '<div class="rsv-reader-cell">
                    <div class="rsv-reader-avatar" style="background:' . $color . '18;color:' . $color . '">'
                        . $inicial . '</div>
                    <span class="rsv-reader-name" title="' . e($nombre) . '">' . e($nombre) . '</span>
                </div>';
            })
            ->addColumn('estado', function ($row) {
                return match ((int) $row->estado) {
                    0 => '<span class="reservation-pill reservation-pill--warning"><i class="bi bi-hourglass-split"></i> EN ESPERA</span>',
                    1 => '<span class="reservation-pill reservation-pill--success"><i class="bi bi-check-circle-fill"></i> ATENDIDO</span>',
                    2 => '<span class="reservation-pill reservation-pill--neutral"><i class="bi bi-slash-circle"></i> CANCELADO</span>',
                    default => '<span class="reservation-pill reservation-pill--neutral">DESCONOCIDO</span>',
                };
            })
            ->addColumn('prestamo', function ($row) {
                return (int) $row->prestamo === 1
                    ? '<span class="rsv-tipo-pill rsv-tipo-pill--casa"><i class="bi bi-house-fill"></i> A CASA</span>'
                    : '<span class="rsv-tipo-pill rsv-tipo-pill--sala"><i class="bi bi-building"></i> EN SALA</span>';
            })
            ->addColumn('acciones', function ($row) {
                if ((int) $row->estado !== 0) return '';

                $libro       = $row->ejemplar->libro;
                $titulo      = e($libro->titulo ?? '');
                $lector      = e($row->lector->name ?? '');
                $tipo        = (int) $row->prestamo === 1 ? 'A CASA' : 'EN SALA';
                $codigo      = e($libro->codigo ?: ($libro->codigo_dewey ?: ''));
                $isbn        = e($libro->isbn ?? '');
                $edicionParts = array_filter([$libro->edicion, $libro->anio_edicion]);
                $edicion     = e(implode(' · ', $edicionParts));
                $autores     = e(
                    ($libro->autores ?? collect())
                        ->map(fn ($a) => trim($a->apellidos.', '.$a->nombres))
                        ->filter()
                        ->implode(' / ')
                );
                $codEjemplar = e($row->ejemplar->codigo_dewey
                    ? $row->ejemplar->codigo_dewey.$row->ejemplar->tipo.$row->ejemplar->codigo_interno
                    : ($row->ejemplar->codigo_ant ?? ''));

                return '<button class="btn btn-sm btn-success entregarReserva"
                            data-id="'.$row->id.'"
                            data-libro="'.$titulo.'"
                            data-lector="'.$lector.'"
                            data-tipo="'.$tipo.'"
                            data-codigo="'.$codigo.'"
                            data-isbn="'.$isbn.'"
                            data-edicion="'.$edicion.'"
                            data-autores="'.$autores.'"
                            data-ejemplar="'.$codEjemplar.'">
                            <i class="bi bi-box-arrow-in-right"></i> Entregar
                        </button>';
            })
            ->rawColumns(['acciones', 'fecha', 'fecha_limite', 'libro', 'ejemplar', 'lector', 'estado', 'prestamo'])
            ->toJson();
    }

    public function nuevaReserva(Request $request)
    {
        if (! auth()->check()) {
            return response()->json([
                'error' => 'Debes iniciar sesión',
            ], 401);
        }

        $request->validate([
            'ejemplar_id' => 'required|exists:ejemplares,id',
            'tipo_prestamo' => 'required|integer|in:0,1',
        ]);

        $resultado = DB::transaction(function () use ($request) {
            $tieneSancionVigente = Sancion::where('user_id', auth()->id())
                ->where('estado', 1)
                ->whereDate('fecha_fin', '>=', now()->toDateString())
                ->exists();

            if ($tieneSancionVigente) {
                return [
                    'status' => 422,
                    'payload' => ['error' => 'No puedes realizar reservas porque tienes una sancion vigente.'],
                ];
            }

            $ejemplar = Ejemplar::with('libro')
                ->lockForUpdate()
                ->find($request->ejemplar_id);

            if (! $ejemplar) {
                return [
                    'status' => 404,
                    'payload' => ['error' => 'Ejemplar no encontrado'],
                ];
            }

            $existe = Reservacion::where('lector_id', auth()->id())
                ->where('estado', 0)
                ->whereHas('ejemplar', function ($q) use ($ejemplar) {
                    $q->where('libro_id', $ejemplar->libro_id);
                })
                ->lockForUpdate()
                ->exists();

            if ($existe) {
                return [
                    'status' => 422,
                    'payload' => ['error' => 'Ya tienes una reserva pendiente de este libro'],
                ];
            }

            if ((int) $ejemplar->estado !== 1) {
                return [
                    'status' => 422,
                    'payload' => ['error' => 'El ejemplar ya fue reservado o prestado'],
                ];
            }

            if ((int) $ejemplar->estado_traslado === Ejemplar::TRASLADO_PENDIENTE) {
                return [
                    'status' => 422,
                    'payload' => ['error' => 'El ejemplar tiene un traslado pendiente y no puede reservarse.'],
                ];
            }

            $fechaReserva = now();
            $fechaLimite = Carbon::tomorrow()->setTime(20, 0, 0);

            Reservacion::create([
                'ejemplar_id' => $ejemplar->id,
                'lector_id' => auth()->id(),
                'fecha_reservacion' => $fechaReserva,
                'fecha_limite' => $fechaLimite,
                'duracion' => 1,
                'prestamo' => $request->tipo_prestamo,
                'bibliotecario_id' => null,
                'estado' => 0,
            ]);

            $ejemplar->estado = 2;
            $ejemplar->save();

            return [
                'status' => 200,
                'payload' => ['ok' => 'Reserva valida hasta manana a las 20:00'],
            ];
        });

        return response()->json($resultado['payload'], $resultado['status']);
    }

    public function cancelarReserva($id)
    {
        $resultado = DB::transaction(function () use ($id) {
            $reserva = Reservacion::where('id', $id)
                ->where('lector_id', auth()->id())
                ->lockForUpdate()
                ->first();

            if (! $reserva) {
                return [
                    'status' => 404,
                    'payload' => ['error' => 'Reserva no encontrada'],
                ];
            }

            if ((int) $reserva->estado !== 0) {
                return [
                    'status' => 422,
                    'payload' => ['error' => 'Solo puedes cancelar reservas en espera'],
                ];
            }

            $reserva->estado = 2;
            $reserva->save();

            $ejemplar = Ejemplar::lockForUpdate()->find($reserva->ejemplar_id);
            $ejemplar->estado = 1;
            $ejemplar->save();

            return [
                'status' => 200,
                'payload' => ['ok' => 'Reserva cancelada correctamente'],
            ];
        });

        return response()->json($resultado['payload'], $resultado['status']);
    }

    public function entregar(Request $request, $id)
    {
        if (! auth()->check()) {
            return response()->json(['error' => 'Debes iniciar sesión'], 401);
        }

        $request->validate([
            'dias' => 'required|integer|min:1',
            'observaciones' => 'nullable|string',
        ]);

        $user = auth()->user();
        $dias = (int) $request->dias;

        $resultado = DB::transaction(function () use ($id, $user, $dias, $request) {
            [$bibliotecasAsignadas, $accesoGlobal] = $this->resolverBibliotecasUsuario($user->id);

            $reserva = Reservacion::with('ejemplar')->lockForUpdate()->findOrFail($id);

            if (! $accesoGlobal && ! $bibliotecasAsignadas->contains((int) optional($reserva->ejemplar)->biblioteca_id)) {
                return [
                    'status' => 403,
                    'payload' => ['error' => 'No puedes entregar reservas de otra biblioteca'],
                ];
            }

            if ((int) $reserva->estado !== 0) {
                return [
                    'status' => 400,
                    'payload' => ['error' => 'No se puede entregar esta reserva'],
                ];
            }

            $reserva->estado = 1;
            $reserva->bibliotecario_id = $user->id;
            $reserva->save();

            $prestamo = new Prestamo;
            $prestamo->lector_id = $reserva->lector_id;
            $prestamo->prestamo_lugar = $reserva->prestamo;
            $prestamo->duracion = $dias;
            $prestamo->fecha_prestamo = now();
            $prestamo->fecha_limite = now()->addDays($dias);
            $prestamo->observaciones_prestamo = $request->observaciones;
            $prestamo->ejemplar_id = $reserva->ejemplar_id;
            $prestamo->estado = 1;
            $prestamo->estado_prestamo = 0;
            $prestamo->user_id = $user->id;
            $prestamo->save();

            $ejemplar = Ejemplar::lockForUpdate()->find($reserva->ejemplar_id);
            $ejemplar->estado = 0;
            $ejemplar->save();

            return [
                'status' => 200,
                'payload' => ['success' => 'Reserva entregada correctamente'],
            ];
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
