<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use App\Models\Dewey;
use App\Models\Libro;
class LibroController extends Controller
{
    //
    
    public function listar(Request $request)
    {

        $query = Libro::with(['autores','tipo_registro'])
                    ->withCount('ejemplares');

        if ($request->has('search') && !empty($request->search['value'])) {

            $search = $request->search['value'];

            $query->where(function($q) use ($search) {

                $q->where('titulo', 'like', "%{$search}%")
                ->orWhere('isbn', 'like', "%{$search}%")
                ->orWhere('codigo', 'like', "%{$search}%");

            });
        }

        return DataTables::of($query)
            ->addColumn('acciones', function($row){
                $btns = '<a href="/administracion/ejemplares/'.$row->id.'" class="btn btn-sm btn-primary me-1 verEjemplares">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" /><path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" /><line x1="16" y1="5" x2="19" y2="8" /></svg>
                </a>';
                $btns .= '<a href="/inventario/libros_nuevo" class="btn btn-sm btn-primaryñ me-1 editarLibro">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" /><path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" /><line x1="16" y1="5" x2="19" y2="8" /></svg>
                </a>';
                $btns .= '<button href="/inventario/libros_nuevo" class="btn btn-sm btn-danger me-1 eliminarLibro">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-trash-x"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7h16" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1
                0 0 1 1 1v3" /><line x1="10" y1="12" x2="14" y2="16" /><line x1="14" y1="12" x2="10" y2="16" /></svg>
                </button>';
                return $btns; 
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }

    public function buscar(Request $request)
    {
        $search = $request->get('q');

        $libros = Libro::with(['autor','editorial'])
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
                    'autor' => $libro->autor->nombre ?? '',
                    'editorial' => $libro->editorial->nombre ?? '',
                    'imagen' => $libro->imagen 
                        ? asset('storage/'.$libro->imagen) 
                        : null,
                ];
            })
        );
    }

    public function nuevo(Request $request){

        $request->validate([
            'titulo' => 'required',
            'tipo_registro_id' => 'required',
            'codigo' => 'required',
            'codigo_dewey' => 'required'
        ]);

        DB::beginTransaction();

        try {

            //obtenemos el valor de dewey
            $dewey=Dewey::find($request->codigo_dewey);
            $libro = new Libro();
            $libro->isbn = $request->isbn;
            $libro->tipo_registro_id = $request->tipo_registro_id;
            $libro->codigo_dewey = $dewey->codigo;
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
                $archivo = $request->file('imagen');
                $nombre = time().'_'.$archivo->getClientOriginalName();
                $archivo->storeAs('libros',$nombre,'public');
                $libro->imagen = 'storage/libros/'.$nombre;
            }

            // subir pdf
            if($request->hasFile('archivo_indice')){
                $archivo = $request->file('archivo_indice');
                $nombre = time().'_'.$archivo->getClientOriginalName();
                $archivo->storeAs('indices', $nombre, 'public');
                $libro->archivo_indice = 'storage/indices/'.$nombre;
            }

            $libro->save();

            // autores
            if($request->autor_id){
                $libro->autores()->sync($request->autor_id);
            }

            // materias
            if($request->materias){
                $libro->materias()->sync($request->materias);
            }

            DB::commit();

            return response()->json([
                'success'=>true
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success'=>false,
                'mensaje'=>$e->getMessage()
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
