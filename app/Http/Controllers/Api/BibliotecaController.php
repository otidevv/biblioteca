<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Biblioteca;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
class BibliotecaController extends Controller
{
    //
    public function listar(Request $request)
    {
       
        $query = Biblioteca::get();

        return DataTables::of($query)
            ->addColumn('acciones', function($row) {
                $btns = '<button class="btn btn-sm btn-primary me-1 editarBiblioteca">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" /><path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" /><line x1="16" y1="5" x2="19" y2="8" /></svg>
                </button>';
                $btns .= '<button class="btn btn-sm btn-danger me-1 eliminarBiblioteca">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-trash-x"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7h16" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /><path d="M10 12l4 4m0 -4l-4 4" /></svg>
                </button>';
                return $btns;
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }
    public function nuevo(Request $request)
    {
        $request->validate([
            // BIBLIOTECA
            'codigo'            => 'required|string|max:20|unique:bibliotecas,codigo',
            'nombre'            => 'required|string|max:150',
            'direccion'         => 'nullable|string|max:255',
            'descripcion'       => 'nullable|string|max:500',
        ]);
        //return $request;    
        DB::beginTransaction();

        try {

            /** =========================
             *  BIBLIOTECA
             *  ========================= */
            $biblioteca = Biblioteca::create([
                'codigo'        => $request->codigo,
                'nombre'        => $request->nombre,
                'direccion'     => $request->direccion,
                'descripcion'   => $request->descripcion,
            ]);
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Biblioteca registrada correctamente',
                'data' => $biblioteca
            ], 201);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al registrar biblioteca',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    public function edit(Request $request)
    {
        $request->validate([
            // BIBLIOTECA
            'id'                => 'required|exists:bibliotecas,id',
            'codigo'            => 'required|string|max:20|unique:bibliotecas,codigo,'.$request->id,
            'nombre'            => 'required|string|max:150',
            'direccion'         => 'nullable|string|max:255',
            'descripcion'       => 'nullable|string|max:500',
        ]);
        DB::beginTransaction();
        try {

            /** =========================
             *  BIBLIOTECA
             *  ========================= */
            $biblioteca = Biblioteca::where('id', $request->id)->first();
            $biblioteca->update([
                'codigo'        => $request->codigo,
                'nombre'        => $request->nombre,
                'direccion'     => $request->direccion,
                'descripcion'   => $request->descripcion,
            ]);
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Biblioteca actualizada correctamente',
                'data' => $biblioteca
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar biblioteca',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:bibliotecas,id',
        ]);
        $biblioteca = Biblioteca::where('id', $request->id)->first();
        $biblioteca->delete();

        return response()->json([
            'success' => true,
            'message' => 'Biblioteca eliminada correctamente',
        ], 200);
    }
}
