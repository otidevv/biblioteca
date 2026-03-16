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
        $query = Compra::with('proveedor','compra_detalles.libro','compra_detalles.ejemplares');

        return DataTables::of($query)
            ->addColumn('acciones', function($row) {
                $btns = '<button class="btn btn-sm btn-primary me-1 verCompra">
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
                        'estado' => 'DISPONIBLE'
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
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ],500);
        }
    }
    public function edith(Request $request)
    {

        $request->validate([
            'id' => 'required',
            'titulo' => 'required',
            'tipo_registro_id' => 'required',
            'codigo' => 'required',
            'codigo_dewey' => 'required'
        ]);

        $libro = Libro::find($request->id);

        if(!$libro){
            return response()->json([
                'success'=>false,
                'message'=>'Libro no encontrado'
            ],404);
        }

        $libro->isbn = $request->isbn;
        $libro->tipo_registro_id = $request->tipo_registro_id;
        $libro->codigo_dewey = $request->codigo_dewey;
        $libro->codigo = $request->codigo;
        $libro->titulo = $request->titulo;
        $libro->editorial_id = $request->editorial_id;
        $libro->edicion = $request->edicion;
        $libro->anio_edicion = $request->anio_edicion;
        $libro->idioma = $request->idioma;
        $libro->paginas = $request->paginas;
        $libro->fecha_publicacion = $request->fecha_publicacion;
        $libro->lugar_publicacion = $request->lugar_publicacion;
        $libro->resumen = $request->resumen;
        $libro->anotaciones = $request->anotaciones;

        // subir imagen
        if($request->hasFile('imagen')){

            if($libro->imagen){
                Storage::delete('public/libros/'.$libro->imagen);
            }

            $archivo = $request->file('imagen');
            $nombre = time().'_'.$archivo->getClientOriginalName();
            $archivo->storeAs('public/libros',$nombre);

            $libro->imagen = $nombre;
        }

        // subir pdf
        if($request->hasFile('archivo_indice')){

            if($libro->archivo_indice){
                Storage::delete('public/indices/'.$libro->archivo_indice);
            }

            $archivo = $request->file('archivo_indice');
            $nombre = time().'_'.$archivo->getClientOriginalName();
            $archivo->storeAs('public/indices',$nombre);

            $libro->archivo_indice = $nombre;
        }

        $libro->save();

        // sincronizar autores
        $libro->autores()->sync($request->autor_id ?? []);

        // sincronizar materias
        $libro->materias()->sync($request->materias ?? []);

        return response()->json([
            'success'=>true,
            'message'=>'Libro actualizado correctamente'
        ]);

    }
}
