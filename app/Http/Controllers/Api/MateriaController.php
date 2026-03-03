<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Materia;
class MateriaController extends Controller
{
    //
    public function listarMaterias(Request $request)
    {         
        $query = Materia::query();
        if ($request->has('q')) {
            $search = $request->q;
            $query->where('nombre', 'like', "%$search%");
        }
        $materias = $query->select('id', 'nombre')->get();
        return response()->json($materias);
    }
    //metodo para crear nueva materia
    public function nuevo(Request $request)
    {
        $request->validate([
            // MATERIA
            'nombre'=> 'required|string|max:150|unique:materias,nombre  ',
            'abreviatura'=> 'nullable|string|max:20',
            'codigo' => 'nullable|string|max:20|unique:materias,codigo',
            'descripcion' => 'nullable|string|max:500',
        ]);
        $materia = Materia::create([
            'nombre'        => $request->nombre,
            'abreviatura'   => $request->abreviatura,
            'codigo'        => $request->codigo,
            'descripcion'   => $request->descripcion,
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Materia registrada correctamente',
            'data' => $materia
        ], 201);
    }
    //metodo para listar materias con datatables
    public function listar(Request $request)
    {        
        $query = Materia::select('id', 'nombre');
        return datatables()->of($query)
            ->addColumn('acciones', function ($materia) {
                return '<div class="d-flex justify-content-center">
                    <button class="btn btn-sm btn-primary me-2 editar-materia" data-id="' . $materia->id . '">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox
                        ="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M9 7h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" />
                            <path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" />
                            <line x1="16" y1="5" x2="19" y2="8" />
                        </svg>
                    </button>
                    <button class="btn btn-sm btn-danger eliminar-materia" data-id="' . $materia->id . '">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-trash-x">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M4 7h16" />
                            <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                            <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                            <line x1="10" y1="12" x2="14" y2="16" />
                            <line x1="14" y1="12" x2="10" y2="16" />
                        </svg>
                    </button>
                </div>';
            })
            ->rawColumns(['acciones'])
            ->make(true);   
    }
}
