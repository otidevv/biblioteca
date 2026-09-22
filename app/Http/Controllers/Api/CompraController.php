<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;  
use App\Models\Compra;
use App\Models\Compra_detalle;
use App\Models\Ejemplar;
use App\Models\Libro;

class CompraController extends Controller
{
    //
            
    public function listarCompras(Request $request)
    {
        $query = Compra::with('proveedor','compra_detalles.libro','compra_detalles.ejemplares')
            ->latest('fecha_compra')
            ->latest('id');

        return DataTables::of($query)
            ->addColumn('acciones', function($row) {
                return '<button type="button" class="btn btn-sm btn-primary verCompra" data-id="' . $row->id . '">Ver compra</button>';
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }

    public function ver(int $id)
    {
        $compra = Compra::with([
            'proveedor',
            'compra_detalles.libro',
            'compra_detalles.ejemplares.biblioteca',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $compra,
        ]);
    }
    public function buscar(Request $request)
    {
        $search = $request->get('q');
        $libros = Libro::with(['autores','editorial'])
            ->when($search, function ($query) use ($search) {
                $query->where('titulo', 'like', "%{$search}%");
            })
            ->limit(20)
            ->get();
        return response()->json(
            $libros->map(function ($libro) {
                return [
                    'id' => $libro->id,
                    'text' => $libro->titulo,
                    // autores como array
                    'autores' => $libro->autores->map(function($autor){
                        return [
                            'id' => $autor->id,
                            'nombre' => $autor->nombres.' '.$autor->apellidos
                        ];
                    }),
                    'editorial' => $libro->editorial
                        ? [
                            'id' => $libro->editorial->id,
                            'nombre' => $libro->editorial->nombre
                        ]
                        : null,

                    'imagen' => $libro->imagen
                        ? asset($libro->imagen)
                        : null,
                ];
            })
        );
    }
    public function guardarCompra(Request $request){
        $request->validate([
            'siaf' => 'required|string|max:50',
            'fecha_compra' => 'required|date',
            'proveedor_id' => 'required|exists:proveedores,id',
            'observaciones' => 'nullable|string',
            'detalle' => 'required|array|min:1',
            'detalle.*.libro_id' => 'required|exists:libros,id',
            'detalle.*.cantidad' => 'required|integer|min:1',
            'detalle.*.precio' => 'required|numeric|min:0.01',
            'detalle.*.subtotal' => 'required|numeric|min:0.01',
        ]);
        
        DB::beginTransaction();
        try {
            // ==============================
            // CALCULAR MONTO TOTAL
            // ==============================
            $total = 0;
            foreach ($request->detalle as $item) {

                $total += $item['subtotal'];

            }
            // ==============================
            // CREAR COMPRA
            // ==============================
            $compra = Compra::create([
                'numero_siaf' => $request->siaf,
                'fecha_compra' => $request->fecha_compra,
                'proveedor_id' => $request->proveedor_id,
                'usuario_id' => auth()->id(),
                'monto_total' => $total,
                'observaciones' => $request->observaciones

            ]);
            // ==============================
            // GUARDAR DETALLES
            // ==============================
            foreach ($request->detalle as $item) {
                $detalle = Compra_detalle::create([
                    'compra_id' => $compra->id,
                    'libro_id' => $item['libro_id'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio'],
                    'monto_total' => $item['subtotal']
                ]);
                // ==============================
                // OBTENER CODIGO DEWEY DEL LIBRO
                // ==============================
                $libro = Libro::find($item['libro_id']);
                // ==============================
                // GENERAR EJEMPLARES
                // ==============================
                for ($i = 1; $i <= $item['cantidad']; $i++) {
                    $ultimoCodigo = Ejemplar::max('codigo_interno') ?? 0;
                    Ejemplar::create([
                        'codigo_interno' => $ultimoCodigo + 1,
                        'codigo_dewey' => $libro->codigo_dewey.$libro->codigo,
                        'tipo' => 'Ej.',
                        'siaf' => $request->siaf,
                        'libro_id' => $libro->id,
                        'biblioteca_id' => null,
                        'compra_detalle_id' => $detalle->id,
                        'estado' => 1
                    ]);
                }
            }
            DB::commit();
            return response()->json([
                'success' => true,
                'mensaje' => 'Compra registrada correctamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->jsonServerError('Error al registrar la compra.', $e, 500, [
                'action' => 'guardarCompra',
            ]);
        }
    }
}
