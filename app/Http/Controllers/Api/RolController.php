<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
 use App\Models\Rol;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
class RolController extends Controller
{
    //
    public function listar(Request $request)
    {
       
        $query = Rol::withCount([
            'users as total_usuarios',
            'permisos as total_permisos'
        ])
        ->with([
            'permisos:id,nombre,permiso_id,codigo'
        ]);

        return DataTables::of($query)
            ->addColumn('acciones', function($row) {
                $btns = '<button class="btn btn-sm btn-primary me-1 editarRol">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" /><path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" /><line x1="16" y1="5" x2="19" y2="8" /></svg>
                </button>';
                $btns .= '<button class="btn btn-sm btn-danger me-1 permisosRol">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-automatic-gearbox"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 17v4h1a2 2 0 1 0 0 -4h-1" /><path d="M17 11h1.5a1.5 1.5 0 0 0 0 -3h-1.5v5" /><path d="M3 5a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M5 7v3a1 1 0 0 0 1 1h3v7a1 1 0 0 0 1 1h3" /><path d="M9 11h4" /></svg>
                </button>';
                $btns .= '<button class="btn btn-sm btn-danger me-1 eliminarRol">
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
            // ROL
            'nombre'            => 'required|string|max:150|unique:roles,nombre',
            'descripcion'       => 'nullable|string|max:500',
        ]);
        //return $request;    
        DB::beginTransaction();

        try {

            /** =========================
             *  ROL
             *  ========================= */
            $rol = Rol::create([
                'nombre'        => $request->nombre,
                'descripcion'   => $request->descripcion,
            ]);
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Rol registrado correctamente',
                'data' => $rol
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
       // return "hola desde edit";
        $request->validate([
            // ROL
            'id'                => 'required|exists:roles,id',
            'nombre'            => 'required|string|max:150|unique:roles,nombre,'.$request->id,
            'descripcion'       => 'nullable|string|max:500',
        ]);
        DB::beginTransaction();
        try {

            /** =========================
             *  ROL
             *  ========================= */
            $rol = Rol::where('id', $request->id)->first();
            $rol->update([
                'nombre'        => $request->nombre,
                'direccion'     => $request->direccion,
                'descripcion'   => $request->descripcion,
            ]);
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Rol actualizado correctamente',
                'data' => $rol
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar rol',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:roles,id',
        ]);
        $rol = Rol::where('id', $request->id)->first();
        $rol->delete();

        return response()->json([
            'success' => true,
            'message' => 'Rol eliminado correctamente',
        ], 200);
    }
    //ADMINISTRA LOS PERMISOS DE UN ROL
    public function guardarPermisos(Request $request)
    {
        $request->validate([
            'rol_id'      => 'required|exists:roles,id',
            'permisos'    => 'nullable|array',
            'permisos.*'  => 'exists:permisos,id',
        ]);

        $rol = Rol::findOrFail($request->rol_id);

        // 🔥 Sync de permisos
        $rol->permisos()->sync($request->permisos ?? []);

        return response()->json([
            'success' => true,
            'message' => 'Permisos actualizados correctamente',
        ]);
    }

}
