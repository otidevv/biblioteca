<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Actividad;
use App\Models\ActividadCategoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class ActividadController extends Controller
{
    public function listar(Request $request)
    {
        $query = Actividad::with('categoria')->latest('fecha_inicio')->latest('id');

        return DataTables::eloquent($query)
            ->addColumn('categoria_nombre', fn ($row) => $row->categoria?->nombre ?: 'Sin categoria')
            ->addColumn('destacado_badge', function ($row) {
                return '<span class="badge ' . ($row->destacado ? 'bg-warning' : 'bg-secondary') . '">' .
                    ($row->destacado ? 'Destacada' : 'Normal') .
                    '</span>';
            })
            ->addColumn('estado_badge', function ($row) {
                return '<span class="badge ' . ((int) $row->estado === 1 ? 'bg-success' : 'bg-secondary') . '">' .
                    ((int) $row->estado === 1 ? 'Activa' : 'Inactiva') .
                    '</span>';
            })
            ->addColumn('imagen_preview', function ($row) {
                if (!$row->imagen) {
                    return '<span class="activities-table__image-empty">Sin imagen</span>';
                }

                $ruta = ltrim((string) $row->imagen, '/');
                $src = str_starts_with($ruta, 'http')
                    ? $ruta
                    : (str_starts_with($ruta, 'storage/') ? '/' . $ruta : '/storage/' . $ruta);

                return '<img src="' . e($src) . '" alt="Imagen actividad" class="activities-table__image">';
            })
            ->addColumn('acciones', fn ($row) => '<button type="button" class="btn btn-sm btn-primary editarActividad">Editar</button>')
            ->rawColumns(['destacado_badge', 'estado_badge', 'imagen_preview', 'acciones'])
            ->make(true);
    }

    public function listarCategorias(Request $request)
    {
        $query = ActividadCategoria::query()->withCount('actividades')->latest('id');

        return DataTables::eloquent($query)
            ->addColumn('estado_badge', function ($row) {
                return '<span class="badge ' . ((int) $row->estado === 1 ? 'bg-success' : 'bg-secondary') . '">' .
                    ((int) $row->estado === 1 ? 'Activa' : 'Inactiva') .
                    '</span>';
            })
            ->addColumn('acciones', fn ($row) => '<button type="button" class="btn btn-sm btn-primary editarCategoriaActividad">Editar</button>')
            ->rawColumns(['estado_badge', 'acciones'])
            ->make(true);
    }

    public function nuevo(Request $request)
    {
        $data = $this->validar($request);

        DB::beginTransaction();
        try {
            if ($request->hasFile('imagen')) {
                $data['imagen'] = $request->file('imagen')->store('actividades', 'public');
            }

            $data['user_id'] = auth()->id();
            $actividad = Actividad::create($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Actividad registrada correctamente.',
                'data' => $actividad->load('categoria'),
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la actividad.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:actividades,id',
        ]);

        $data = $this->validar($request, true);

        DB::beginTransaction();
        try {
            $actividad = Actividad::findOrFail($request->id);

            if ($request->hasFile('imagen')) {
                if ($actividad->imagen) {
                    Storage::disk('public')->delete($actividad->imagen);
                }

                $data['imagen'] = $request->file('imagen')->store('actividades', 'public');
            }

            $actividad->update($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Actividad actualizada correctamente.',
                'data' => $actividad->fresh()->load('categoria'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la actividad.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function nuevaCategoria(Request $request)
    {
        $data = $this->validarCategoria($request);

        DB::beginTransaction();
        try {
            $categoria = ActividadCategoria::create($data + [
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Categoria registrada correctamente.',
                'data' => $categoria,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la categoria.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function editarCategoria(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:actividad_categorias,id',
        ]);

        $data = $this->validarCategoria($request, true);

        DB::beginTransaction();
        try {
            $categoria = ActividadCategoria::findOrFail($request->id);
            $categoria->update($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Categoria actualizada correctamente.',
                'data' => $categoria,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la categoria.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    protected function validar(Request $request, bool $editing = false): array
    {
        $data = $request->validate([
            'actividad_categoria_id' => 'required|exists:actividad_categorias,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'hora_inicio' => 'nullable|date_format:H:i',
            'hora_fin' => 'nullable|date_format:H:i',
            'titulo' => 'required|string|max:150',
            'resumen' => 'nullable|string|max:500',
            'contenido' => 'nullable|string',
            'referencia' => 'nullable|string|max:255',
            'lugar' => 'nullable|string|max:255',
            'modalidad' => 'nullable|string|max:100',
            'destacado' => 'nullable|boolean',
            'estado' => 'required|boolean',
            'imagen' => ($editing ? 'nullable' : 'nullable') . '|image|max:3072',
        ]);

        $data['fecha_fin'] = $data['fecha_fin'] ?? $data['fecha_inicio'];
        $data['destacado'] = (bool) ($data['destacado'] ?? false);

        return $data;
    }

    protected function validarCategoria(Request $request, bool $editing = false): array
    {
        $categoriaId = $request->input('id');

        return $request->validate([
            'abreviatura' => 'required|string|max:20|unique:actividad_categorias,abreviatura,' . ($editing ? $categoriaId : 'NULL') . ',id',
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'estado' => 'required|boolean',
        ]);
    }
}
