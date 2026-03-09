<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Libro;
class LibroController extends Controller
{
    //
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
        $libro = new Libro();
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
            $archivo = $request->file('imagen');
            $nombre = time().'_'.$archivo->getClientOriginalName();
            $archivo->storeAs('public/libros',$nombre);
            $libro->imagen = $nombre;
        }
        // subir pdf
        if($request->hasFile('archivo_indice')){
            $archivo = $request->file('archivo_indice');
            $nombre = time().'_'.$archivo->getClientOriginalName();
            $archivo->storeAs('public/indices',$nombre);
            $libro->archivo_indice = $nombre;
        }
        $libro->save();
        // autores (tabla pivote)
        if($request->autor_id){
            $libro->autores()->sync($request->autor_id);
        }
        // materias
        if($request->materias){
            $libro->materias()->sync($request->materias);
        }
        return response()->json([
            'success'=>true
        ]);
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
