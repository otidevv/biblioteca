<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Prestamo;
use App\Models\Ejemplar;

class PrestamoController extends Controller
{
    //
    public function nuevoPrestamo(Request $request)
    {
        // 🔐 validar usuario
        if (!auth()->check()) {
            return response()->json(['error' => 'Debes iniciar sesión'], 401);
        }

        // ✅ validación
        $request->validate([
            'libro_id' => 'required',
            'lector_id' => 'required',
            'prestamo_lugar' => 'required',
            'duracion' => 'required|integer|min:1'
        ]);

        // 🚫 limitar préstamos activos
        $activos = Prestamo::where('lector_id', $request->lector_id)
            ->where('estado', 'prestado')
            ->count();

        if ($activos >= 3) {
            return response()->json([
                'error' => 'Ya tienes 3 préstamos activos'
            ]);
        }

        // 📦 buscar ejemplar disponible
        $ejemplar = Ejemplar::where('libro_id', $request->libro_id)
            ->where('estado', '1') // disponible
            ->first();

        if (!$ejemplar) {
            return response()->json([
                'error' => 'No hay ejemplares disponibles'
            ]);
        }

        // 📅 fechas
        $fechaPrestamo = now();
        $fechaLimite = now()->addDays($request->duracion);

        // 💾 guardar préstamo
        Prestamo::create([
            'lector_id' => $request->lector_id,
            'user_id' => auth()->id(),
            'prestamo_lugar' => $request->prestamo_lugar,
            'duracion' => $request->duracion,
            'fecha_prestamo' => $fechaPrestamo,
            'fecha_limite' => $fechaLimite,
            'observaciones' => null,
            'estado' => 'prestado'
        ]);

        // 🔄 actualizar ejemplar
        $ejemplar->update([
            'estado' => '0'
        ]);

        return response()->json([
            'ok' => '📚 Préstamo registrado correctamente'
        ]);
    }
}
