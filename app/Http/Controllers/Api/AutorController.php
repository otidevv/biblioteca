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
        $query = Autor::with('pais');

        // Filtrar por nombre o apellido
        if ($request->has('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                $q->where('nombres', 'like', "%{$search}%")
                ->orWhere('apellidos', 'like', "%{$search}%");
            });
        }

        return DataTables::of($query)
            ->addColumn('acciones', function($row) {
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
        //return $request;
        $request->validate([
            // AUTOR
            'nombre'            => 'required|string|max:20',
            'apellidos'            => 'required|string|max:150',
        ]);
        //return $request;    
        DB::beginTransaction();

        try {

            /** =========================
             *  AUTOR
             *  ========================= */
            $autor = Autor::create([
                'nombres'        => $request->nombre,
                'apellidos'        => $request->apellidos,
                'pais'     => $request->pais,
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
        $request->validate([
            // AUTOR
            'id'                => 'required|exists:autores,id',
            'nombre'            => 'required|string|max:20',
            'apellidos'            => 'required|string|max:150',
        ]);
        DB::beginTransaction();
        try {

            /** =========================
             *  AUTOR
             *  ========================= */
            $autor = Autor::where('id', $request->id)->first();
            $autor->update([
                'nombres'        => $request->nombre,
                'apellidos'        => $request->apellidos,
                'pais'     => $request->pais,
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

    public function destroy(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:autores,id',
        ]);
        $autor = Autor::where('id', $request->id)->first();
        $autor->delete();

        return response()->json([
            'success' => true,
            'message' => 'Autor eliminado correctamente',
        ], 200);
    }
    // metodos para select2 en nuevo libro
    public function listarAutores(Request $request)
    {
        $query = Autor::query()->limit(10);;

        if ($request->has('q')) {
            $search = $request->q;
            $query->where(function($q) use ($search) {
                $q->where('nombres', 'LIKE', "%$search%")
                ->orWhere('apellidos', 'LIKE', "%$search%");
            })->limit(10);
        }
        $autores = $query->get(['id', 'nombres', 'apellidos'])->map(function($autor) {
            return [
                'id' => $autor->id,
                'text' => $autor->nombres . ' ' . $autor->apellidos,
                'nombres' => $autor->nombres,
                'apellidos' => $autor->apellidos
            ];
        });
        return response()->json($autores);
    }
}
