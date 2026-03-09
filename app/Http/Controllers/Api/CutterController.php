<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class CutterController extends Controller
{
    //
    public function codigoCutter(Request $request)
    {
        $letras = strtoupper($request->get('letras', ''));

        $cutter = DB::table('codido_cutters')
            ->where('nombre', $letras) // suponiendo que en tu tabla hay columna 'letra' con las 3 primeras letras
            ->first();

        if(!$cutter){
            return response()->json(['codigo' => 'no encontrado']); // fallback si no hay registro
        }

        return response()->json(['codigo' => $cutter->codigo]);
    }
    public function checkCodigo(Request $request)
    {
        $codigo = $request->codigo;

        $libro = DB::table('libros')
            ->join('autor_libros','autor_libros.libro_id','=','libros.id')
            ->join('autores','autores.id','=','autor_libros.autor_id')
            ->where('libros.codigo','like',$codigo.'%')
            ->select(
                'libros.id',
                'autores.id as autor_id',
                'autores.apellidos',
                'autores.nombres'
            )
            ->first();

        if(!$libro){
            return response()->json([
                'existe'=>false
            ]);
        }

        return response()->json([
            'existe'=>true,
            'autor_id'=>$libro->autor_id,
            'apellido'=>$libro->apellidos,
            'nombre'=>$libro->nombres
        ]);
    }
}
