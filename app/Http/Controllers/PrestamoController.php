<?php

namespace App\Http\Controllers;

use App\Models\Prestamo;
use App\Models\Usuario_rol_biblioteca;
use Illuminate\Support\Facades\Auth;

class PrestamoController extends Controller
{
    public function index(string $modulo)
    {
        return match ($modulo) {
            'registro' => $this->registro(),
            'historial' => $this->historial(),
            'reservas' => $this->reservas(),
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
