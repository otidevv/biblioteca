<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use App\Models\Libro;
use App\Models\Ejemplar;

class EjemplarController extends Controller
{
    //
    public function listar(Request $request)
    {
        $query = Ejemplar::with(['detalle_compra.compra','biblioteca']);
        // filtro por biblioteca
        if($request->filled('biblioteca_id')){
            $query->where('biblioteca_id',$request->biblioteca_id);
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
                return $row->biblioteca ? $row->biblioteca->nombre : '';
            })
            ->addColumn('acciones', function($row){
                $btns = '<a href="/administracion/ejemplares/'.$row->id.'" class="btn btn-sm btn-primary me-1">
                Ver</a>';
                $btns .= '<button data-id="'.$row->id.'" class="btn btn-sm btn-danger eliminarEjemplar">
                Eliminar</button>';
                return $btns;
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }

    public function guardar(Request $request)
    {
        $request->validate([
            'cantidad' => 'required|integer|min:1',
            'libro_id' => 'required',
            'biblioteca_id' => 'required'
        ]);
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
                    'codigo_dewey' => $libro->codigo_dewey
                ]);
            }
            DB::commit();
            return response()->json([
                'success' => true
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}
