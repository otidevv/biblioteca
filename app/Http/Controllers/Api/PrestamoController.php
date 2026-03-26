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
        ->addColumn('fecha_prestamo', function($row) {
            return $row->fecha_prestamo;
        })
        ->addColumn('fecha_limite_raw', function($row) {
            $fechaBase = Carbon::parse($row->fecha_prestamo)
                            ->addDays($row->duracion);
            $fechaLimite = $fechaBase->copy()->setTime(20, 0, 0);
            return $fechaLimite->toDateTimeString(); // 🔥 formato JS compatible
        })
        ->addColumn('fecha_limite', function($row) {
            $fechaBase = Carbon::parse($row->fecha_prestamo)
                            ->addDays($row->duracion);
            $fechaLimite = $fechaBase->copy()->setTime(20, 0, 0);
            return '<small class="text-muted">'.$fechaLimite->format('d/m/Y H:i').'</small>';
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

    $now = Carbon::now();

    // 📅 Fecha base + duración
    $fechaBase = Carbon::parse($row->fecha_prestamo)
                    ->addDays($row->duracion);

    // ⏰ Límite 20:00
    $fechaLimite = $fechaBase->copy()->setTime(20, 0, 0);

    // ⏳ Diferencia en segundos
    $diff = $now->diffInSeconds($fechaLimite, false);

    // 🔴 FUERA DE PLAZO
    if ($now->greaterThan($fechaLimite)&& $row->estado==1) {
        return '
        <div>
            <span class="badge bg-danger">FUERA DE PLAZO</span><br>
        </div>';
    }

    // 🟡 EN CURSO CON CONTADOR
    if ($row->estado == 1) {

        $clase = $diff < 86400 ? 'text-danger fw-bold' : 'text-success';

        return '
        <div>
            <span class="countdown '.$clase.'" data-seconds="'.$diff.'"></span>
        </div>';
    }

    // 🟢 FINALIZADO
    if ($row->estado == 2) {
        return '<span class="badge bg-success">FINALIZADO</span>';
    }

    return '<span class="badge bg-secondary">--</span>';
})
        ->addColumn('estado_prestamo', function($row) {
            switch($row->estado_prestamo) {
                case 0: return '<span style="color:white" class="badge bg-warning">PRESTADO</span>';
                case 1: return '<span style="color:white" class="badge bg-success">DEVUELTO</span>';
                case 2: return '<span style="color:white" class="badge bg-danger">TARDANZA</span>';
                case 3: return '<span style="color:white" class="badge bg-dark">DETERIORO</span>';
            }
        })
        ->addColumn('prestamo_lugar', function($row) {
            return $row->prestamo == 1 ? 'A casa' : 'En sala';
        })
        ->addColumn('acciones', function($row){
            return $row->estado === 1 ? 
                '<button class="btn btn-sm btn-success devolverPrestamo" data-id="'.$row->id.'">
                    <i class="fas fa-check"></i> Devoler
                </button>' 
                : '';
        })
        ->rawColumns(['acciones','fecha_limite','estado','estado_prestamo'])
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
   
    public function devolver(Request $request, $id)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Debes iniciar sesión'], 401);
        }

        $prestamo = Prestamo::with('ejemplar')->find($id);

        if (!$prestamo) {
            return response()->json(['error' => 'No se encontró préstamo']);
        }

        $now = Carbon::now();

        // 🔥 calcular fecha límite
        $fechaLimite = Carbon::parse($prestamo->fecha_prestamo)
            ->addDays($prestamo->duracion)
            ->setTime(20, 0, 0);

        //validar si está fuera de plazo
        $estadoPrestamo = ($now->greaterThan($fechaLimite)) ? 2 : 1;

        //  actualizar préstamo
        $prestamo->update([
            'fecha_devolucion' => $now,
            'comentario_devolucion' => $request->observaciones,
            'estado' => 2,
            'estado_prestamo' => $estadoPrestamo,
            'estado_libro' => $request->estado_libro
        ]);

        // actualizar ejemplar
        $prestamo->ejemplar->update([
            'estado' => 1 // disponible
        ]);

        return response()->json([
            'ok' => '📚 Devolución registrada correctamente'
        ]);
    }
}
