<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Models\Autor;
use App\Models\Materia;
use App\Models\Editorial;
use App\Models\Idioma;
use App\Models\Ejemplar;
use App\Models\Tipo_registro;
use App\Models\Comentario;
use App\Models\Reservacion;

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
        'ejemplar_id' => 'required|exists:ejemplares,id',
    ]);

    // 🔍 Obtener ejemplar
    $ejemplar = Ejemplar::with('libro')->find($request->ejemplar_id);

    if (!$ejemplar) {
        return response()->json([
            'error' => 'Ejemplar no encontrado'
        ]);
    }

    // 🚫 Verificar reserva duplicada del mismo libro
    $existe = Reservacion::where('lector_id', auth()->id())
        ->where('estado', 'pendiente')
        ->whereHas('ejemplar', function($q) use ($ejemplar){
            $q->where('libro_id', $ejemplar->libro_id);
        })
        ->exists();

    if ($existe) {
        return response()->json([
            'error' => 'Ya tienes una reserva pendiente de este libro'
        ]);
    }

    // 🚫 Verificar disponibilidad
    if ($ejemplar->estado != 1) {
        return response()->json([
            'error' => 'El ejemplar ya fue reservado o prestado'
        ]);
    }

    // ⏳ FECHAS CORRECTAS
    $fechaReserva = now(); // fecha actual

    $fechaLimite = Carbon::tomorrow()->setTime(20, 0, 0); 
    // mañana a las 20:00

    // 💾 Guardar reservación
    Reservacion::create([
        'ejemplar_id' => $ejemplar->id,
        'lector_id' => auth()->id(),
        'fecha_reservacion' => $fechaReserva,
        'fecha_limite' => $fechaLimite,
        'duracion' => 1,
        'prestamo' => $request->tipo_prestamo,
        'bibliotecario_id' => null,
        'estado' => 0
    ]);

    // 🔄 Cambiar estado del ejemplar
    $ejemplar->estado = 2; // reservado
    $ejemplar->save();

    return response()->json([
        'ok' => 'Reserva válida hasta mañana a las 20:00'
    ]);
}
public function cancelarReserva($id)
{
    $reserva = Reservacion::where('id', $id)
        ->where('lector_id', auth()->id())
        ->first();

    if (!$reserva) {
        return response()->json([
            'error' => 'Reserva no encontrada'
        ]);
    }

    if ($reserva->estado != 0) {
        return response()->json([
            'error' => 'Solo puedes cancelar reservas en espera'
        ]);
    }

    // 🔄 Cambiar estado a cancelado
    $reserva->estado = 2;
    $reserva->save();

    // 📦 Liberar ejemplar
    $ejemplar = $reserva->ejemplar;
    $ejemplar->estado = 1; // disponible
    $ejemplar->save();

    return response()->json([
        'ok' => 'Reserva cancelada correctamente'
    ]);
}
}
