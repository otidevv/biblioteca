<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ejemplar;
use App\Models\Prestamo;
use App\Models\Reservacion;
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
        $permiso = Usuario_rol_biblioteca::where('rol_id', 19)
            ->where('user_id', $user->id)
            ->first();

        $query = Reservacion::with(['ejemplar.libro', 'lector'])
            ->where('estado', 0)
            ->when($permiso && $permiso->biblioteca_id, function ($q) use ($permiso) {
                $q->whereHas('ejemplar', function ($sub) use ($permiso) {
                    $sub->where('biblioteca_id', $permiso->biblioteca_id);
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
                return '<span class="reservation-table__date">' . $row->created_at->format('d/m/Y') . '</span>';
            })
            ->addColumn('fecha_limite', function ($row) {
                $now = Carbon::now();
                $fechaLimite = Carbon::parse($row->fecha_reservacion)
                    ->addDay()
                    ->setTime(20, 0, 0);

                $diff = $now->diffInSeconds($fechaLimite, false);

                if ($diff <= 0) {
                    return '<span class="reservation-pill reservation-pill--danger">VENCIDO</span>';
                }

                return '<span class="countdown reservation-countdown" data-seconds="'.$diff.'"></span>';
            })
            ->addColumn('libro', function ($row) {
                $titulo = $row->ejemplar->libro->titulo ?? 'Libro no disponible';

                return '<div class="reservation-table__book" title="' . e($titulo) . '">' . e($titulo) . '</div>';
            })
            ->addColumn('ejemplar', function ($row) {
                $codigo = $row->ejemplar->codigo_dewey
                    ? $row->ejemplar->codigo_dewey.$row->ejemplar->tipo.$row->ejemplar->codigo_interno
                    : $row->ejemplar->codigo_ant;

                return '<span class="reservation-table__code">' . e($codigo ?: '-') . '</span>';
            })
            ->addColumn('lector', function ($row) {
                $lector = $row->lector->name ?? 'Lector no disponible';

                return '<div class="reservation-table__reader" title="' . e($lector) . '">' . e($lector) . '</div>';
            })
            ->addColumn('estado', function ($row) {
                return match ((int) $row->estado) {
                    0 => '<span class="reservation-pill reservation-pill--warning">EN ESPERA</span>',
                    1 => '<span class="reservation-pill reservation-pill--success">ATENDIDO</span>',
                    2 => '<span class="reservation-pill reservation-pill--neutral">CANCELADO</span>',
                    default => '<span class="reservation-pill reservation-pill--neutral">DESCONOCIDO</span>',
                };
            })
            ->addColumn('prestamo', function ($row) {
                return (int) $row->prestamo === 1
                    ? '<span class="reservation-pill reservation-pill--info">A CASA</span>'
                    : '<span class="reservation-pill reservation-pill--info">EN SALA</span>';
            })
            ->addColumn('acciones', function ($row) {
                return $row->estado === 0
                    ? '<button class="btn btn-sm btn-success entregarReserva" data-id="'.$row->id.'">
                        <i class="fas fa-check"></i> Entregar
                    </button>'
                    : '';
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
            $reserva = Reservacion::lockForUpdate()->findOrFail($id);

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
}
