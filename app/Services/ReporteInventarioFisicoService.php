<?php

namespace App\Services;

use App\Models\Biblioteca;
use App\Models\User;
use App\Models\Usuario_rol_biblioteca;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReporteInventarioFisicoService
{
    public function resolverContextoBibliotecas(User $usuario): array
    {
        $asignaciones = Usuario_rol_biblioteca::query()
            ->where('user_id', $usuario->id)
            ->where('estado', 1)
            ->pluck('biblioteca_id')
            ->unique()
            ->values();

        $bibliotecasAsignadas = $asignaciones
            ->filter(fn ($id) => !is_null($id))
            ->map(fn ($id) => (int) $id)
            ->values();

        $accesoGlobal = $bibliotecasAsignadas->isEmpty() && $asignaciones->contains(null);

        return [
            'asignaciones' => $asignaciones,
            'bibliotecasAsignadas' => $bibliotecasAsignadas,
            'accesoGlobal' => $accesoGlobal,
            'bibliotecaFijaId' => !$accesoGlobal && $bibliotecasAsignadas->count() === 1 ? $bibliotecasAsignadas->first() : null,
            'puedeFiltrarBiblioteca' => $accesoGlobal || $bibliotecasAsignadas->count() > 1,
        ];
    }

    public function obtenerBibliotecasVisibles(User $usuario): Collection
    {
        $contexto = $this->resolverContextoBibliotecas($usuario);

        return $contexto['accesoGlobal']
            ? Biblioteca::query()->orderBy('nombre')->get(['id', 'nombre'])
            : Biblioteca::query()
                ->whereIn('id', $contexto['bibliotecasAsignadas']->all())
                ->orderBy('nombre')
                ->get(['id', 'nombre']);
    }

    public function resolverNombreBibliotecaReporte(User $usuario, $bibliotecaId): string
    {
        $contexto = $this->resolverContextoBibliotecas($usuario);

        if ($bibliotecaId === 'sin_biblioteca') {
            return 'Sin biblioteca';
        }

        if (!empty($bibliotecaId)) {
            if (!$contexto['accesoGlobal'] && !$contexto['bibliotecasAsignadas']->contains((int) $bibliotecaId)) {
                return 'Biblioteca no permitida';
            }

            return Biblioteca::query()->where('id', $bibliotecaId)->value('nombre') ?? 'Biblioteca seleccionada';
        }

        if ($contexto['bibliotecaFijaId']) {
            return Biblioteca::query()->where('id', $contexto['bibliotecaFijaId'])->value('nombre') ?? 'Biblioteca asignada';
        }

        return 'Todas las bibliotecas visibles';
    }

    public function consultaReporte(User $usuario, $bibliotecaId = null)
    {
        $contexto = $this->resolverContextoBibliotecas($usuario);

        $ejemplares = DB::table('ejemplares')
            ->selectRaw('libro_id, COUNT(*) as total_ejemplares');

        if ($contexto['accesoGlobal']) {
            if (!empty($bibliotecaId)) {
                if ((string) $bibliotecaId === 'sin_biblioteca') {
                    $ejemplares->whereNull('biblioteca_id');
                } else {
                    $ejemplares->where('biblioteca_id', (int) $bibliotecaId);
                }
            }
        } elseif ($contexto['bibliotecasAsignadas']->isNotEmpty()) {
            $ejemplares->whereIn('biblioteca_id', $contexto['bibliotecasAsignadas']->all());
        } else {
            $ejemplares->whereRaw('1 = 0');
        }

        $ejemplares->groupBy('libro_id');

        $autores = DB::table('autor_libros')
            ->join('autores', 'autores.id', '=', 'autor_libros.autor_id')
            ->selectRaw("autor_libros.libro_id, GROUP_CONCAT(DISTINCT TRIM(CONCAT(COALESCE(autores.apellidos, ''), ' ', COALESCE(autores.nombres, ''))) ORDER BY autores.apellidos, autores.nombres SEPARATOR ', ') as autores")
            ->groupBy('autor_libros.libro_id');

        $materias = DB::table('libro_materias')
            ->join('materias', 'materias.id', '=', 'libro_materias.materia_id')
            ->selectRaw("libro_materias.libro_id, GROUP_CONCAT(DISTINCT materias.nombre ORDER BY materias.nombre SEPARATOR ', ') as materias")
            ->groupBy('libro_materias.libro_id');

        return DB::table('libros')
            ->joinSub($ejemplares, 'inv', function ($join) {
                $join->on('inv.libro_id', '=', 'libros.id');
            })
            ->leftJoinSub($autores, 'aut', function ($join) {
                $join->on('aut.libro_id', '=', 'libros.id');
            })
            ->leftJoinSub($materias, 'mat', function ($join) {
                $join->on('mat.libro_id', '=', 'libros.id');
            })
            ->leftJoin('idiomas', 'idiomas.id', '=', 'libros.idioma')
            ->leftJoin('editoriales', 'editoriales.id', '=', 'libros.editorial_id')
            ->select([
                'libros.id',
                'libros.codigo',
                'libros.codigo_ant',
                'libros.codigo_dewey',
                'libros.titulo',
                'libros.anio_edicion',
                'libros.edicion',
                'libros.isbn',
                'libros.lugar_publicacion',
                'libros.paginas',
                'libros.fecha_publicacion',
                'libros.anotaciones',
                'editoriales.nombre as editorial_nombre',
                'idiomas.nombre as idioma_nombre',
                'inv.total_ejemplares',
                'aut.autores',
                'mat.materias',
            ])
            ->orderBy('libros.titulo');
    }
}
