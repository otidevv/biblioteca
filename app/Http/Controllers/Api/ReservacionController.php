<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ejemplar;
use App\Models\Prestamo;
use App\Models\Reservacion;
use App\Models\Usuario_rol_biblioteca;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ReservacionController extends Controller
{
    public function listar(Request $request)
    {
        if (! auth()->check()) {
            return response()->json(['error' => 'Debes iniciar sesion'], 401);
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
                return $row->created_at->format('d/m/Y');
            })
            ->addColumn('fecha_limite', function ($row) {
                $now = Carbon::now();
                $fechaLimite = Carbon::parse($row->fecha_reservacion)
                    ->addDay()
                    ->setTime(20, 0, 0);

                $diff = $now->diffInSeconds($fechaLimite, false);

                if ($diff <= 0) {
                    return '<span class="text-danger fw-bold">Vencido</span>';
                }

                return '<span class="countdown" data-seconds="'.$diff.'"></span>';
            })
            ->addColumn('libro', function ($row) {
                return $row->ejemplar->libro->titulo ?? '';
            })
            ->addColumn('ejemplar', function ($row) {
                return $row->ejemplar->codigo_dewey
                    ? $row->ejemplar->codigo_dewey.$row->ejemplar->tipo.$row->ejemplar->codigo_interno
                    : $row->ejemplar->codigo_ant;
            })
            ->addColumn('lector', function ($row) {
                return $row->lector->name ?? '';
            })
            ->addColumn('estado', function ($row) {
                return match ($row->estado) {
                    0 => 'En espera',
                    1 => 'Atendido',
                    2 => 'Cancelado',
                    default => 'Desconocido',
                };
            })
            ->addColumn('prestamo', function ($row) {
                return $row->prestamo == 1 ? 'A casa' : 'En sala';
            })
            ->addColumn('acciones', function ($row) {
                return $row->estado === 0
                    ? '<button class="btn btn-sm btn-success entregarReserva" data-id="'.$row->id.'">
                        <i class="fas fa-check"></i> Entregar
                    </button>'
                    : '';
            })
            ->rawColumns(['acciones', 'fecha_limite'])
            ->toJson();
    }

    public function nuevaReserva(Request $request)
    {
        if (! auth()->check()) {
            return response()->json([
                'error' => 'Debes iniciar sesion',
            ], 401);
        }

        $request->validate([
            'ejemplar_id' => 'required|exists:ejemplares,id',
            'tipo_prestamo' => 'required|integer|in:0,1',
        ]);

        $ejemplar = Ejemplar::with('libro')->find($request->ejemplar_id);

        if (! $ejemplar) {
            return response()->json([
                'error' => 'Ejemplar no encontrado',
            ], 404);
        }

        $existe = Reservacion::where('lector_id', auth()->id())
            ->where('estado', 0)
            ->whereHas('ejemplar', function ($q) use ($ejemplar) {
                $q->where('libro_id', $ejemplar->libro_id);
            })
            ->exists();

        if ($existe) {
            return response()->json([
                'error' => 'Ya tienes una reserva pendiente de este libro',
            ], 422);
        }

        if ($ejemplar->estado != 1) {
            return response()->json([
                'error' => 'El ejemplar ya fue reservado o prestado',
            ], 422);
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

        return response()->json([
            'ok' => 'Reserva valida hasta manana a las 20:00',
        ]);
    }

    public function cancelarReserva($id)
    {
        $reserva = Reservacion::where('id', $id)
            ->where('lector_id', auth()->id())
            ->first();

        if (! $reserva) {
            return response()->json([
                'error' => 'Reserva no encontrada',
            ], 404);
        }

        if ($reserva->estado != 0) {
            return response()->json([
                'error' => 'Solo puedes cancelar reservas en espera',
            ], 422);
        }

        $reserva->estado = 2;
        $reserva->save();

        $ejemplar = $reserva->ejemplar;
        $ejemplar->estado = 1;
        $ejemplar->save();

        return response()->json([
            'ok' => 'Reserva cancelada correctamente',
        ]);
    }

    public function entregar(Request $request, $id)
    {
        if (! auth()->check()) {
            return response()->json(['error' => 'Debes iniciar sesion'], 401);
        }

        $request->validate([
            'dias' => 'required|integer|min:1',
            'observaciones' => 'nullable|string',
        ]);

        $user = auth()->user();
        $dias = (int) $request->dias;
        $reserva = Reservacion::findOrFail($id);

        if ($reserva->estado != 0) {
            return response()->json(['error' => 'No se puede entregar esta reserva'], 400);
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

        $ejemplar = Ejemplar::find($reserva->ejemplar_id);
        $ejemplar->estado = 0;
        $ejemplar->save();

        return response()->json(['success' => 'Reserva entregada correctamente']);
    }
}
