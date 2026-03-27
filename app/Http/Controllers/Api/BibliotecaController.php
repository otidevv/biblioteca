<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Biblioteca;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
            'codigo'        => 'required|string|max:20|unique:bibliotecas,codigo',
            'nombre'        => 'required|string|max:150',
            'direccion'     => 'nullable|string|max:255',
            'descripcion'   => 'nullable|string|max:500',
            'imagen'        => 'nullable',
        ]);

        DB::beginTransaction();

        try {

            $rutaImagen = null;

            if ($request->hasFile('imagen')) {
                $rutaImagen = $request->file('imagen')->store('bibliotecas', 'public');
            }

            $biblioteca = Biblioteca::create([
                'codigo'        => $request->codigo,
                'nombre'        => $request->nombre,
                'direccion'     => $request->direccion,
                'descripcion'   => $request->descripcion,
                'imagen'        => 'storage/'.$rutaImagen,
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
                'message' => 'Error al registrar',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    public function edit(Request $request)
    {
        $request->validate([
            'id'            => 'required|exists:bibliotecas,id',
            'codigo'        => 'required|string|max:20|unique:bibliotecas,codigo,' . $request->id,
            'nombre'        => 'required|string|max:150',
            'direccion'     => 'nullable|string|max:255',
            'descripcion'   => 'nullable|string|max:500',
            'imagen'        => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        DB::beginTransaction();

        try {

            $biblioteca = Biblioteca::find($request->id);

            if ($request->hasFile('imagen')) {

                // eliminar anterior
                if ($biblioteca->imagen && Storage::disk('public')->exists($biblioteca->imagen)) {
                    Storage::disk('public')->delete($biblioteca->imagen);
                }

                $rutaImagen = $request->file('imagen')->store('bibliotecas', 'public');

            } else {
                $rutaImagen = $biblioteca->imagen;
            }

            $biblioteca->update([
                'codigo'        => $request->codigo,
                'nombre'        => $request->nombre,
                'direccion'     => $request->direccion,
                'descripcion'   => $request->descripcion,
                'imagen'        => 'storage/'.$rutaImagen,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Biblioteca actualizada correctamente',
                'data' => $biblioteca
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar',
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
