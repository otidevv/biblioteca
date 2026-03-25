<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

use App\Models\Prestamo;
use App\Models\Ejemplar;
use App\Models\Usuario_rol_biblioteca;
use Auth;
class PrestamoController extends Controller
{
    //
    public function listar(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Debes iniciar sesión'], 401);
        }
        $user = Auth::user();
        $permiso = Usuario_rol_biblioteca::where('rol_id', 19)
            ->where('user_id', $user->id)
            ->first();

        $query = Prestamo::with(['ejemplar.libro', 'lector'])
            ->when($permiso && $permiso->biblioteca_id, function($q) use ($permiso) {
                $q->whereHas('ejemplar', function($sub) use ($permiso) {
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
        ->addColumn('fecha', function($row) {
            return $row->created_at->format('d/m/Y');
        })
        ->addColumn('fecha_limite', function($row) {

            $now = \Carbon\Carbon::now();

            // 🔥 Fecha límite: día siguiente a las 8:00 PM
            $fechaLimite = \Carbon\Carbon::parse($row->fecha_reservacion)
                                ->addDay()
                                ->setTime(20, 0, 0); // 20 = 8 PM

            $diff = $now->diffInSeconds($fechaLimite, false);

            if ($diff <= 0) {
                return '<span class="text-danger fw-bold">Vencido</span>';
            }

            return '<span class="countdown" data-seconds="'.$diff.'"></span>';
        })
        ->addColumn('libro', function($row) {
            return $row->ejemplar->libro->titulo ?? '';
        })
        ->addColumn('ejemplar', function($row) {
            return $row->ejemplar->codigo_dewey? $row->ejemplar->codigo_dewey.$row->ejemplar->tipo.$row->ejemplar->codigo_interno:$row->ejemplar->codigo_ant;
        })
        ->addColumn('lector', function($row) {
            return $row->lector->name ?? '';
        })
        ->addColumn('estado', function($row) {
            switch($row->estado) {
                case 1: return 'INICIADO';
                case 2: return 'FINALIZADO';
            }
        })
        ->addColumn('estado_prestamo', function($row) {
            switch($row->estado) {
                case 0: return 'PRESTADO';
                case 1: return 'DEVUELTO';
                case 2: return 'TARDANZA';
                case 3: return 'DETERIORO';
            }
        })
        ->addColumn('prestamo_lugar', function($row) {
            return $row->prestamo == 1 ? 'A casa' : 'En sala';
        })
        ->addColumn('acciones', function($row){
            return $row->estado === 0 ? 
                '<button class="btn btn-sm btn-success entregarLibro" data-id="'.$row->id.'">
                    <i class="fas fa-check"></i> Devoler
                </button>' 
                : '';
        })
        ->rawColumns(['acciones','fecha_limite'])
        ->toJson();
    }
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
