<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Proveedor;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ProveedorController extends Controller
{
    //
    //
    public function listar(Request $request)
    {
       
        $query = Proveedor::get();

        return DataTables::of($query)
            ->addColumn('acciones', function($row) {
                $btns = '<button class="btn btn-sm btn-primary me-1 editarProveedor">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" /><path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" /><line x1="16" y1="5" x2="19" y2="8" /></svg>
                </button>';
                $btns .= '<button class="btn btn-sm btn-danger me-1 eliminarProveedor">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-trash-x"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7h16" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1
    0 0 1 1 1v3" /><line x1="10" y1="12" x2="14" y2="16" /><line x1="14" y1="12" x2="10" y2="16" /></svg>
                </button>';
                return $btns;   
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }
    public function nuevo(Request $request)
    {
        //return $request;
        $request->validate([
            // PROVEEDOR
            'tipo_documento'            => 'required|string|max:20',
            'responsable'            => 'required|string|max:150',
            'telefono'            => 'required|string|max:20',
            'nro_documento'            => 'required|string|max:20|unique:proveedores,nro_documento',
        ]);
        //return $request;    
        DB::beginTransaction();

        try {

            /** =========================
             *  PROVEEDOR
             *  ========================= */
            $proveedor = Proveedor::create([
                'tipo_documento'        => $request->tipo_documento,
                'responsable'        => $request->responsable,
                'telefono'     => $request->telefono,
                'nro_documento'     => $request->nro_documento,
                'razon_social'     => $request->razon_social,
                'web'     => $request->web,
                'direccion'     => $request->direccion,
            ]);
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Proveedor registrado correctamente',
                'data' => $proveedor
            ], 201);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al registrar proveedor',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    public function edit(Request $request)
    {
        $request->validate([
            // PROVEEDOR
            'id'                => 'required|exists:proveedores,id',
            'nro_documento'            => 'required|string|max:20|unique:proveedores,nro_documento,'.$request->id,
            'telefono'            => 'required|string|max:20',        
            'tipo_documento'            => 'required|string|max:20',
            'razon_social'     => 'nullable|string|max:255',
        ]);
        DB::beginTransaction();
        try {

            /** =========================
             *  PROVEEDOR
             *  ========================= */
            $proveedor = Proveedor::where('id', $request->id)->first();
            $proveedor->update([
                'tipo_documento'        => $request->tipo_documento,
                'nro_documento'        => $request->nro_documento,
                'responsable'        => $request->responsable,
                'telefono'     => $request->telefono,
                'razon_social'     => $request->razon_social,
                'web'     => $request->web,
                'direccion'     => $request->direccion,
            ]);
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Proveedor actualizado correctamente',
                'data' => $proveedor
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar proveedor',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:proveedores,id',
        ]);
        $proveedor = Proveedor::where('id', $request->id)->first();
        $proveedor->delete();

        return response()->json([
            'success' => true,
            'message' => 'Proveedor eliminado correctamente',
        ], 200);
    }
}
