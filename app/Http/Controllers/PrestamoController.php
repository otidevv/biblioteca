<?php

namespace App\Http\Controllers;

use App\Models\Prestamo;
use App\Models\Sancion;
use App\Models\TipoSancion;
use App\Models\User;
use App\Models\Usuario_rol_biblioteca;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrestamoController extends Controller
{
    public function index(string $modulo)
    {
        return match ($modulo) {
            'registro' => $this->registro(),
            'historial' => $this->historial(),
            'reservas' => $this->reservas(),
            'multas' => $this->multas(),
            default => abort(404),
        };
    }

    protected function reservas()
    {
        return view('prestamos.reserva');
    }

    protected function registro()
    {
        return view('prestamos.prestamos');
    }

    protected function multas()
    {
        $busqueda = trim((string) request('q', ''));
        $estado = request('estado');
        $lectorId = request('lector_id');
        $tipo = trim((string) request('tipo', ''));
        $fechaDesde = request('fecha_desde');
        $fechaHasta = request('fecha_hasta');

        $query = Sancion::with([
            'usuario',
            'bibliotecario',
            'tipoSancion',
            'prestamo.ejemplar.libro',
            'prestamo.ejemplar.biblioteca',
            'reservacion.ejemplar.libro',
            'reservacion.ejemplar.biblioteca',
        ]);

        if ($busqueda !== '') {
            $query->where(function ($subQuery) use ($busqueda) {
                $subQuery->whereHas('usuario', function ($q) use ($busqueda) {
                    $q->where('name', 'like', '%' . $busqueda . '%')
                        ->orWhere('email', 'like', '%' . $busqueda . '%');
                })->orWhereHas('bibliotecario', function ($q) use ($busqueda) {
                    $q->where('name', 'like', '%' . $busqueda . '%');
                })->orWhereHas('prestamo.ejemplar.libro', function ($q) use ($busqueda) {
                    $q->where('titulo', 'like', '%' . $busqueda . '%');
                })->orWhereHas('reservacion.ejemplar.libro', function ($q) use ($busqueda) {
                    $q->where('titulo', 'like', '%' . $busqueda . '%');
                })->orWhere('motivo', 'like', '%' . $busqueda . '%')
                    ->orWhere('tipo', 'like', '%' . $busqueda . '%')
                    ->orWhere('codigo_pago', 'like', '%' . $busqueda . '%')
                    ->orWhere('id', $busqueda);
            });
        }

        if ($estado !== null && $estado !== '') {
            $query->where('estado', (int) $estado);
        }

        if ($lectorId !== null && $lectorId !== '') {
            $query->where('user_id', (int) $lectorId);
        }

        if ($tipo !== '') {
            $query->where('tipo', 'like', '%' . $tipo . '%');
        }

        if (! empty($fechaDesde)) {
            $query->whereDate('fecha_inicio', '>=', $fechaDesde);
        }

        if (! empty($fechaHasta)) {
            $query->whereDate('fecha_inicio', '<=', $fechaHasta);
        }

        $sanciones = $query
            ->latest('fecha_inicio')
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        $resumen = [
            'total' => (clone $query)->count(),
            'activas' => (clone $query)->where('estado', 1)->count(),
            'cerradas' => (clone $query)->where('estado', 2)->count(),
        ];

        $lectorFiltro = $lectorId
            ? User::with('persona')->find($lectorId)
            : null;
        $lectorFormulario = request()->old('lector_id')
            ? User::with('persona')->find(request()->old('lector_id'))
            : null;

        $tiposSancion = TipoSancion::query()
            ->where('estado', true)
            ->orderBy('nombre')
            ->get(['id', 'codigo', 'nombre', 'dias_duracion']);

        return view('prestamos.multas', compact('sanciones', 'resumen', 'lectorFiltro', 'lectorFormulario', 'tiposSancion'));
    }

    public function guardarSancion(Request $request)
    {
        $data = $request->validate([
            'lector_id' => ['required', 'exists:users,id'],
            'tipo_sancion_id' => ['required', 'exists:tipo_sanciones,id'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'motivo' => ['required', 'string', 'max:500'],
        ]);

        $tipoSancion = TipoSancion::findOrFail($data['tipo_sancion_id']);
        $fechaInicio = \Carbon\Carbon::parse($data['fecha_inicio']);
        $fechaFin = \Carbon\Carbon::parse($data['fecha_fin']);

        Sancion::create([
            'user_id' => $data['lector_id'],
            'tipo_sancion_id' => $tipoSancion->id,
            'tipo' => $tipoSancion->codigo,
            'motivo' => $data['motivo'],
            'fecha_inicio' => $fechaInicio->toDateString(),
            'fecha_fin' => $fechaFin->toDateString(),
            'duracion' => $fechaInicio->diffInDays($fechaFin) + 1,
            'observaciones' => 'Sancion registrada manualmente.',
            'bibliotecario_id' => Auth::id(),
            'estado' => 1,
        ]);

        return redirect()
            ->to(url('/prestamos/multas'))
            ->with('status', 'Sancion registrada correctamente.');
    }

    public function buscarLectoresSancion(Request $request)
    {
        $busqueda = trim((string) $request->input('q', ''));

        $lectores = User::with('persona')
            ->whereHas('roles', function ($query) {
                $query->where('roles.id', 5);
            })
            ->when($busqueda !== '', function ($query) use ($busqueda) {
                $query->where(function ($subQuery) use ($busqueda) {
                    $subQuery->where('users.name', 'like', '%' . $busqueda . '%')
                        ->orWhere('users.email', 'like', '%' . $busqueda . '%')
                        ->orWhereHas('persona', function ($personaQuery) use ($busqueda) {
                            $personaQuery->where('dni', 'like', '%' . $busqueda . '%')
                                ->orWhere('nombres', 'like', '%' . $busqueda . '%')
                                ->orWhere('apellido_paterno', 'like', '%' . $busqueda . '%')
                                ->orWhere('apellido_materno', 'like', '%' . $busqueda . '%');
                        });
                });
            })
            ->orderBy('name')
            ->limit(20)
            ->get()
            ->map(function (User $lector) {
                $dni = $lector->persona?->dni;
                $texto = $lector->name . ($dni ? ' - DNI ' . $dni : '') . ' - ' . $lector->email;

                return [
                    'id' => $lector->id,
                    'text' => $texto,
                ];
            });

        return response()->json([
            'results' => $lectores,
        ]);
    }

    public function levantarSancion(Sancion $sancion)
    {
        if ((int) $sancion->estado !== 1) {
            return back()->with('status', 'La sancion ya se encuentra cerrada.');
        }

        $sancion->update([
            'estado' => 2,
            'fecha_fin' => now()->toDateString(),
            'detalles_termino' => trim((string) request('detalles_termino', 'Sancion levantada manualmente.')),
            'bibliotecario_id' => Auth::id(),
        ]);

        return back()->with('status', 'Sancion levantada correctamente.');
    }

    protected function historial()
    {
        $user = Auth::user();
        $permiso = Usuario_rol_biblioteca::where('rol_id', 19)
            ->where('user_id', $user->id)
            ->first();

        $busqueda = trim((string) request('q', ''));
        $estado = request('estado');
        $estadoPrestamo = request('estado_prestamo');
        $fechaDesde = request('fecha_desde');
        $fechaHasta = request('fecha_hasta');

        $query = Prestamo::with(['ejemplar.libro', 'ejemplar.biblioteca', 'lector', 'bibliotecario']);

        if ($permiso && $permiso->biblioteca_id) {
            $query->whereHas('ejemplar', function ($subQuery) use ($permiso) {
                $subQuery->where('biblioteca_id', $permiso->biblioteca_id);
            });
        }

        if ($busqueda !== '') {
            $query->where(function ($subQuery) use ($busqueda) {
                $subQuery->whereHas('ejemplar.libro', function ($q) use ($busqueda) {
                    $q->where('titulo', 'like', '%' . $busqueda . '%');
                })->orWhereHas('ejemplar.biblioteca', function ($q) use ($busqueda) {
                    $q->where('nombre', 'like', '%' . $busqueda . '%');
                })->orWhereHas('lector', function ($q) use ($busqueda) {
                    $q->where('name', 'like', '%' . $busqueda . '%');
                })->orWhereHas('bibliotecario', function ($q) use ($busqueda) {
                    $q->where('name', 'like', '%' . $busqueda . '%');
                })->orWhere('id', $busqueda);
            });
        }

        if ($estado !== null && $estado !== '') {
            $query->where('estado', (int) $estado);
        }

        if ($estadoPrestamo !== null && $estadoPrestamo !== '') {
            $query->where('estado_prestamo', (int) $estadoPrestamo);
        }

        if (! empty($fechaDesde)) {
            $query->whereDate('fecha_prestamo', '>=', $fechaDesde);
        }

        if (! empty($fechaHasta)) {
            $query->whereDate('fecha_prestamo', '<=', $fechaHasta);
        }

        $historial = $query
            ->latest('fecha_prestamo')
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view('lectores.historial', compact('historial'));
    }
}
