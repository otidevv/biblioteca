<?php

namespace App\Services;

use App\Models\User;
use App\Models\Usuario_rol_biblioteca;
use Illuminate\Support\Facades\DB;

class ReporteHistorialPrestamosService
{
    public function resolverBibliotecasPermitidas(User $usuario): array
    {
        $asignaciones = Usuario_rol_biblioteca::query()
            ->where('user_id', $usuario->id)
            ->where('estado', 1)
            ->pluck('biblioteca_id')
            ->unique()
            ->values();

        $bibliotecas = $asignaciones
            ->filter(fn ($id) => !is_null($id))
            ->map(fn ($id) => (int) $id)
            ->values();

        return [
            'bibliotecas' => $bibliotecas,
            'accesoGlobal' => $bibliotecas->isEmpty() && $asignaciones->contains(null),
        ];
    }

    public function consultaReporte(User $usuario, array $filtros = [])
    {
        $contexto = $this->resolverBibliotecasPermitidas($usuario);
        $busqueda = trim((string) ($filtros['q'] ?? ''));
        $estado = $filtros['estado'] ?? null;
        $estadoPrestamo = $filtros['estado_prestamo'] ?? null;
        $fechaDesde = $filtros['fecha_desde'] ?? null;
        $fechaHasta = $filtros['fecha_hasta'] ?? null;

        $query = DB::table('prestamos')
            ->leftJoin('ejemplares', 'ejemplares.id', '=', 'prestamos.ejemplar_id')
            ->leftJoin('libros', 'libros.id', '=', 'ejemplares.libro_id')
            ->leftJoin('bibliotecas', 'bibliotecas.id', '=', 'ejemplares.biblioteca_id')
            ->leftJoin('users as lectores', 'lectores.id', '=', 'prestamos.lector_id')
            ->leftJoin('users as bibliotecarios', 'bibliotecarios.id', '=', 'prestamos.user_id')
            ->select([
                'prestamos.id',
                'prestamos.estado',
                'prestamos.estado_prestamo',
                'prestamos.prestamo_lugar',
                'prestamos.fecha_prestamo',
                'prestamos.fecha_limite',
                'prestamos.fecha_devolucion',
                'prestamos.duracion',
                'prestamos.observaciones_prestamo',
                'prestamos.observaciones_devolucion',
                'libros.titulo',
                'libros.codigo_dewey as libro_codigo_dewey',
                'libros.codigo as libro_codigo',
                'ejemplares.codigo_dewey as ejemplar_codigo_dewey',
                'ejemplares.codigo_ant as ejemplar_codigo_ant',
                'ejemplares.tipo as ejemplar_tipo',
                'ejemplares.codigo_interno as ejemplar_codigo_interno',
                'bibliotecas.nombre as biblioteca_nombre',
                'lectores.name as lector_nombre',
                'bibliotecarios.name as bibliotecario_nombre'
            ]);

        if (!$contexto['accesoGlobal']) {
            if ($contexto['bibliotecas']->isNotEmpty()) {
                $query->whereIn('ejemplares.biblioteca_id', $contexto['bibliotecas']->all());
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        if ($busqueda !== '') {
            $query->where(function ($subQuery) use ($busqueda) {
                $subQuery->where('libros.titulo', 'like', '%' . $busqueda . '%')
                    ->orWhere('bibliotecas.nombre', 'like', '%' . $busqueda . '%')
                    ->orWhere('lectores.name', 'like', '%' . $busqueda . '%')
                    ->orWhere('bibliotecarios.name', 'like', '%' . $busqueda . '%')
                    ->orWhere('prestamos.id', $busqueda);
            });
        }

        if ($estado !== null && $estado !== '') {
            $query->where('prestamos.estado', (int) $estado);
        }

        if ($estadoPrestamo !== null && $estadoPrestamo !== '') {
            $query->where('prestamos.estado_prestamo', (int) $estadoPrestamo);
        }

        if (!empty($fechaDesde)) {
            $query->whereDate('prestamos.fecha_prestamo', '>=', $fechaDesde);
        }

        if (!empty($fechaHasta)) {
            $query->whereDate('prestamos.fecha_prestamo', '<=', $fechaHasta);
        }

        return $query
            ->orderByDesc('prestamos.fecha_prestamo')
            ->orderByDesc('prestamos.id');
    }

    public function describirFiltros(array $filtros): string
    {
        $partes = [];

        if (!empty($filtros['q'])) {
            $partes[] = 'Busqueda: ' . $filtros['q'];
        }

        if (($filtros['estado'] ?? '') !== '') {
            $partes[] = 'Estado general: ' . $this->textoEstadoGeneral((int) $filtros['estado']);
        }

        if (($filtros['estado_prestamo'] ?? '') !== '') {
            $partes[] = 'Estado prestamo: ' . $this->textoEstadoPrestamo((int) $filtros['estado_prestamo']);
        }

        if (!empty($filtros['fecha_desde'])) {
            $partes[] = 'Desde: ' . $filtros['fecha_desde'];
        }

        if (!empty($filtros['fecha_hasta'])) {
            $partes[] = 'Hasta: ' . $filtros['fecha_hasta'];
        }

        return empty($partes) ? 'Sin filtros adicionales' : implode(' | ', $partes);
    }

    public function textoEstadoGeneral(int $estado): string
    {
        return match ($estado) {
            1 => 'Iniciado',
            2 => 'Finalizado',
            default => 'Desconocido',
        };
    }

    public function textoEstadoPrestamo(int $estadoPrestamo): string
    {
        return match ($estadoPrestamo) {
            0 => 'Prestado',
            1 => 'Devuelto',
            2 => 'Tardanza',
            3 => 'Deterioro',
            default => 'Desconocido',
        };
    }

    public function textoTipoPrestamo($valor): string
    {
        return ((int) $valor) === 1 ? 'A casa' : 'En sala';
    }
}
