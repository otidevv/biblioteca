<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use App\Models\Autor;

class AutorController extends Controller
{
    //
    public function listar(Request $request)
    {
        $query = Autor::with('pais')->withCount('libros');

        if ($request->has('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                $q->where('nombres', 'like', "%{$search}%")
                  ->orWhere('apellidos', 'like', "%{$search}%")
                  ->orWhereRaw("CONCAT(nombres, ' ', apellidos) LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("CONCAT(apellidos, ' ', nombres) LIKE ?", ["%{$search}%"]);
            });
        }

        return DataTables::of($query)
            ->addColumn('pais_id', fn($row) => $row->pais)
            ->addColumn('acciones', function() {
                return '
                    <div class="dropdown admin-action-menu">
                        <button class="btn admin-action-menu__trigger" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Abrir acciones">
                            <i class="bi bi-three-dots"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end admin-action-menu__dropdown">
                            <button class="dropdown-item admin-action-link admin-action-link--edit editarAutor" type="button">
                                <i class="bi bi-pencil-square"></i><span>Editar</span>
                            </button>
                            <button class="dropdown-item admin-action-link admin-action-link--delete eliminarAutor" type="button">
                                <i class="bi bi-trash3"></i><span>Eliminar</span>
                            </button>
                        </div>
                    </div>
                ';
            })
            ->editColumn('pais', function($row) {
                return optional($row->pais)->nombre ?? '';
            })
            ->rawColumns(['acciones'])
            ->make(true);

    }
    public function nuevo(Request $request)
    {
        $request->merge(['pais' => $request->pais ?: null]);

        $request->validate([
            'nombre'     => 'required|string|max:100',
            'apellidos'  => 'nullable|string|max:150',
            'pais'       => 'nullable|integer|exists:paises,id',
        ]);

        $existe = Autor::whereRaw('LOWER(nombres) = ?', [strtolower($request->nombre)])
            ->whereRaw('LOWER(apellidos) = ?', [strtolower($request->apellidos ?? '')])
            ->exists();

        if ($existe) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe un autor registrado con ese nombre y apellidos.',
            ], 422);
        }

        DB::beginTransaction();

        try {

            $autor = Autor::create([
                'nombres'   => $request->nombre,
                'apellidos' => $request->apellidos,
                'pais'      => $request->pais ?: null,
            ]);
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Autor registrado correctamente',
                'data' => $autor
            ], 201);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al registrar autor',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    public function edit(Request $request)
    {
        $request->merge(['pais' => $request->pais ?: null]);

        $request->validate([
            'id'         => 'required|exists:autores,id',
            'nombre'     => 'required|string|max:100',
            'apellidos'  => 'nullable|string|max:150',
            'pais'       => 'nullable|integer|exists:paises,id',
        ]);

        $existe = Autor::whereRaw('LOWER(nombres) = ?', [strtolower($request->nombre)])
            ->whereRaw('LOWER(apellidos) = ?', [strtolower($request->apellidos ?? '')])
            ->where('id', '!=', $request->id)
            ->exists();

        if ($existe) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe otro autor registrado con ese nombre y apellidos.',
            ], 422);
        }

        DB::beginTransaction();
        try {

            $autor = Autor::where('id', $request->id)->first();
            $autor->update([
                'nombres'   => $request->nombre,
                'apellidos' => $request->apellidos,
                'pais'      => $request->pais ?: null,
            ]);
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Autor actualizado correctamente',
                'data' => $autor
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar autor',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(int $id)
    {
        $autor = Autor::find($id);

        if (!$autor) {
            return response()->json(['success' => false, 'message' => 'Autor no encontrado'], 404);
        }

        $autor->delete();

        return response()->json([
            'success' => true,
            'message' => 'Autor eliminado correctamente',
        ], 200);
    }
    // metodos para select2 en nuevo libro
    public function listarAutores(Request $request)
    {
        $query = Autor::query()->orderBy('nombres');

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function($q) use ($search) {
                $q->where('nombres', 'LIKE', "%$search%")
                  ->orWhere('apellidos', 'LIKE', "%$search%")
                  ->orWhereRaw("CONCAT(nombres, ' ', apellidos) LIKE ?", ["%$search%"])
                  ->orWhereRaw("CONCAT(apellidos, ' ', nombres) LIKE ?", ["%$search%"]);
            });
        }

        $autores = $query->limit(20)->get(['id', 'nombres', 'apellidos'])->map(fn($autor) => [
            'id'        => $autor->id,
            'text'      => trim($autor->nombres . ' ' . $autor->apellidos),
            'nombres'   => $autor->nombres,
            'apellidos' => $autor->apellidos,
        ]);

        return response()->json($autores);
    }
}
