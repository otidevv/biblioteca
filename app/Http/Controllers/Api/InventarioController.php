<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Compra;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;    

class InventarioController extends Controller
{
    //
    public function listarCompras(Request $request)
    {
       
        $query = Compra::get();

        return DataTables::of($query)
            ->addColumn('acciones', function($row) {
                $btns = '<button class="btn btn-sm btn-primary me-1 editarCompra">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" /><path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" /><line x1="16" y1="5" x2="19" y2="8" /></svg>
                </button>';
                $btns .= '<button class="btn btn-sm btn-danger me-1 eliminarCompra">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-trash-x"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7h16" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1
                    0 0 1 1 1v3" /><path d="M10 12l4 4m0 -4l-4 4" /></svg>  
                </button>';
                return $btns;   
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }
    public function nuevo(Request $request)
    {
        $request->validate([
            // COMPRAS
            'codigo'            => 'required|string|max:20|unique:compras,codigo',
            'nombre'            => 'required|string|max:150',
            'direccion'         => 'nullable|string|max:255',
            'descripcion'       => 'nullable|string|max:500',
        ]);
        //return $request;    
        DB::beginTransaction();

        try {

            /** =========================
             *  COMPRAS
             *  ========================= */
            $compra = Compra::create([
                'codigo'        => $request->codigo,
                'nombre'        => $request->nombre,
                'direccion'     => $request->direccion,
                'descripcion'   => $request->descripcion,
            ]);
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Compra registrada correctamente',
                'data' => $compra
            ], 201);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al registrar compra',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    public function edit(Request $request)
    {
        $request->validate([
            // COMPRAS
            'id'                => 'required|exists:compras,id',
            'codigo'            => 'required|string|max:20|unique:compras,codigo,'.$request->id,
            'nombre'            => 'required|string|max:150',
            'direccion'         => 'nullable|string|max:255',
            'descripcion'       => 'nullable|string|max:500',
        ]);
        DB::beginTransaction();
        try {

            /** =========================
             *  COMPRAS 
             *  ========================= */
            $compra = Compra::where('id', $request->id)->first();
            $compra->update([
                'codigo'        => $request->codigo,
                'nombre'        => $request->nombre,
                'direccion'     => $request->direccion,
                'descripcion'   => $request->descripcion,
            ]);
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Compra actualizada correctamente',
                'data' => $compra
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar compra',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:compras,id',
        ]);
        $compra = Compra::where('id', $request->id)->first();
        $compra->delete();

        return response()->json([
            'success' => true,
            'message' => 'Compra eliminada correctamente',
        ], 200);
    }
}
