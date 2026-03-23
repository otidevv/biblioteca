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

        return DataTables::of($query)

        // 🔥 FILTRO PERSONALIZADO (SOLUCIÓN)
        ->filter(function ($query) use ($request) {

            if ($request->has('search') && $request->search['value'] != '') {

                $search = strtolower($request->search['value']);

                $query->where(function ($q) use ($search) {

                    $q->whereRaw('LOWER(libros.titulo) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(libros.isbn) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(libros.codigo) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(libros.codigo_dewey) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(libros.estado) LIKE ?', ["%{$search}%"])

                    // ✅ AUTORES (RELACIÓN)
                    ->orWhereHas('autores', function ($q2) use ($search) {
                        $q2->whereRaw('LOWER(nombres) LIKE ?', ["%{$search}%"]);
                    })

                    // ✅ TIPO REGISTRO
                    ->orWhereHas('tipo_registro', function ($q3) use ($search) {
                        $q3->whereRaw('LOWER(nombres) LIKE ?', ["%{$search}%"]);
                    });

                });
            }
        })

        // 🔥 COLUMNA AUTORES (VISIBLE EN TABLA)
        ->addColumn('autores', function($row){
            return $row->autores->pluck('nombres')->join(', ');
        })

        ->addColumn('acciones', function($row){
            return '
            <div class="dropdown text-center">
                <button class="btn btn-sm btn-icon btn-light" type="button" data-bs-toggle="dropdown">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" 
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" 
                        stroke-width="2">
                        <circle cx="12" cy="5" r="1"></circle>
                        <circle cx="12" cy="12" r="1"></circle>
                        <circle cx="12" cy="19" r="1"></circle>
                    </svg>
                </button>

                <ul class="dropdown-menu dropdown-menu-end shadow-sm">

                    <li>
                        <a class="dropdown-item verEjemplares" href="/administracion/ejemplares/'.$row->id.'">
                            <i class="fas fa-book text-primary"></i> Ver ejemplares
                        </a>
                    </li>

                    <li>
                        <a class="dropdown-item editarLibro" href="/inventario/libros_nuevo/'.$row->id.'">
                            <i class="fas fa-edit text-warning"></i> Editar
                        </a>
                    </li>

                    <li><hr class="dropdown-divider"></li>

                    <li>
                        <button class="dropdown-item text-danger eliminarLibro" data-id="'.$row->id.'">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </li>

                </ul>
            </div>
            ';
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
