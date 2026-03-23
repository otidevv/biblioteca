<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReservacionController extends Controller
{
    //
    public function nuevaReserva(Request $request)
    {
        if (!auth()->check()) {
            return response()->json([
                'error' => 'Debes iniciar sesión'
            ], 401);
        }

        $request->validate([
            'libro_id' => 'required',
            'duracion' => 'required|integer|min:1'
        ]);

        // 🔒 evitar múltiples reservas activas del mismo libro
        $existe = Reservacion::where('lector_id', auth()->id())
            ->where('estado', 'pendiente')
            ->whereHas('ejemplar', function($q) use ($request){
                $q->where('libro_id', $request->libro_id);
            })
            ->exists();

        if ($existe) {
            return response()->json([
                'error' => 'Ya tienes una reserva pendiente de este libro'
            ]);
        }

        // 📦 buscar ejemplar disponible
        $ejemplar = Ejemplar::where('libro_id', $request->libro_id)
            ->where('estado', '1')
            ->first();

        if (!$ejemplar) {
            return response()->json([
                'error' => 'No hay ejemplares disponibles'
            ]);
        }

        $fecha = now();
        $limite = now()->addDays($request->duracion);

        // 💾 guardar reservación
        Reservacion::create([
            'ejemplar_id' => $request->ejemplar_id,
            'lector_id' => auth()->id(),
            'duracion' => $request->duracion,
            'fecha_reservacion' => now(),
            'fecha_limite' => now()->addDays($request->duracion),
            'prestamo' => 0,
            'bibliotecario_id' => null,
            'estado' => 'pendiente'
        ]);

        return response()->json([
            'ok' => 'Reserva enviada correctamente'
        ]);
    }
}
