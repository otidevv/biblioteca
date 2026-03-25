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
            $fechaBase = Carbon::parse($row->fecha_reservacion)
                            ->addDays($row->duracion);
            $fechaLimite = $fechaBase->copy()->setTime(20, 0, 0);
            return $fechaLimite->toDateTimeString(); // 🔥 formato JS compatible
        })
        ->addColumn('fecha_limite', function($row) {

            $now = Carbon::now();

            // Fecha base + días
            $fechaBase = Carbon::parse($row->fecha_reservacion)
                            ->addDays($row->duracion);

            // Fecha límite a las 20:00
            $fechaLimite = $fechaBase->copy()->setTime(20, 0, 0);

            // 🔥 SOLO comparar por FECHA (sin hora)
            $hoy = $now->toDateString();
            $fechaFin = $fechaBase->toDateString();

            // 🔴 Si ya pasó el día
            if ($hoy > $fechaFin) {
                return '
                <div>
                    <span class="badge bg-danger">Fuera de plazo</span><br>
                    <small class="text-muted">'.$fechaLimite->format('d/m/Y 20:00').'</small>
                </div>';
            }

            // 🟡 Si es el mismo día o falta días → mostrar contador
            $diff = $now->diffInSeconds($fechaLimite, false);

            $clase = $diff < 86400 ? 'text-danger fw-bold' : 'text-success';

            return '
            <div>
                <small class="text-muted">'.$fechaLimite->format('d/m/Y 20:00').'</small><br>
                <span class="countdown '.$clase.'" data-seconds="'.$diff.'"></span>
            </div>';
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
                case 1: return '<span style="color:white" class="badge bg-primary">INICIADO</span>';
                case 2: return '<span style="color:white" class="badge bg-success">FINALIZADO</span>';
                default: return '<span style="color:white" class="badge bg-secondary">--</span>';
            }
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
        $fechaLimite = Carbon::parse($prestamo->fecha_reservacion)
            ->addDays($prestamo->duracion)
            ->setTime(20, 0, 0);

        //validar si está fuera de plazo
        $estadoPrestamo = ($now->greaterThan($fechaLimite)) ? 2 : 1;

        //  actualizar préstamo
        $prestamo->update([
            'fecha_devolucion' => $now,
            'comentario_devolucion' => $request->observaciones,
            'estado_prestamo' => $estadoPrestamo,
            'estado_libro' => $request->estado_libro
        ]);

        // actualizar ejemplar
        $prestamo->ejemplar->update([
            'estado' => 0 // disponible
        ]);

        return response()->json([
            'ok' => '📚 Devolución registrada correctamente'
        ]);
    }
}
