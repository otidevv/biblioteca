<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use App\Models\Libro;
use App\Models\Ejemplar;
use Validator;

class EjemplarController extends Controller
{
    //
    public function listar(Request $request)
    {
        $query = Ejemplar::with(['detalle_compra.compra','biblioteca'])->where('libro_id',$request->id);

        // filtro biblioteca
        if ($request->has('biblioteca_id')) {

            if ($request->biblioteca_id === null) {

                $query->whereNull('biblioteca_id');

            } elseif ($request->biblioteca_id != -1) {

                $query->where('biblioteca_id', $request->biblioteca_id);
            }
        }

        // búsqueda
        if ($request->has('search') && !empty($request->search['value'])) {

            $search = $request->search['value'];

            $query->where(function($q) use ($search){
                $q->where('codigo_interno','like',"%{$search}%")
                ->orWhere('siaf','like',"%{$search}%");
            });
        }

        return DataTables::of($query)

            ->addColumn('biblioteca', function($row){
                return $row->biblioteca ? $row->biblioteca->nombre : 'Sin biblioteca';
            })

            ->addColumn('acciones', function($row){
                return '
                    <button onclick="actualizarEjemplar('.$row->id.')" class="btn btn-sm btn-primary me-1">Actualizar</button>
                    <button data-id="'.$row->id.'" class="btn btn-sm btn-danger eliminarEjemplar">Eliminar</button>
                ';
            })

            ->rawColumns(['acciones'])
            ->make(true);
    }

    public function guardar(Request $request)
    {
        
        $validator = Validator::make($request->all(), [      
            'cantidad' => 'required|integer|min:1',
            'libro_id' => 'required',
            'biblioteca_id' => 'required'   
        ]);
        if ($validator->fails()) {
            return response()->json(['message'=>$validator->errors()], 500);
        }
        DB::beginTransaction();
        try {
            // obtener código del libro
            $libro = Libro::find($request->libro_id);
            // obtener último código del libro
            $ultimoCodigo = Ejemplar::where('libro_id', $request->libro_id)
                            ->max('codigo_interno');
            $ultimoCodigo = $ultimoCodigo ? $ultimoCodigo : 0;
            for ($i = 1; $i <= $request->cantidad; $i++) {
                Ejemplar::create([
                    'libro_id' => $request->libro_id,
                    'biblioteca_id' => $request->biblioteca_id,
                    'codigo_interno' => $ultimoCodigo + $i,
                    'tipo' => 'ej.',
                    'siaf' => $request->siaf,
                    'codigo_dewey' => $libro->codigo_dewey.$libro->codigo
                ]);
            }
            DB::commit();
            return response()->json([
                'success' => true,
                'message'=>'Ejemplares agregados correcamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    public function actualizar(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|min:1',
            'biblioteca_id' => 'required'
        ]);
        DB::beginTransaction();
        try {
            // obtener código del libro
            $ejempar=Ejemplar::find($request->id);
            $ejempar->biblioteca_id = $request->biblioteca_id;
            $ejempar->siaf = $request->siaf;
            $ejempar->save();
            DB::commit();
            return response()->json([
                'success' => true,
                'message'=>'Error al actualizar ejemplar'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => 'error al actualizar'
            ]);
        }
    }
    public function enviarBiblioteca(Request $request)
    {
        Ejemplar::whereIn('id',$request->ejemplares)
                ->update([
                    'biblioteca_id'=>$request->biblioteca_id
                ]);
        return response()->json([
            'success'=>true,
            'message'=>'Ejemplares movidos correctamente'
        ]);

    }
}
