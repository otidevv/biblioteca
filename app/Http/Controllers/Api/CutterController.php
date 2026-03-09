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
        $apellido = $request->get('apellido', '');
        $cutter = $request->get('cutter', '');

        // Buscamos coincidencias en la tabla libros
        $existe = DB::table('libros')
            ->where('codigo', 'like', $apellido.'%'.$cutter.'%')
            ->exists();

        return response()->json(['existe' => $existe]);
    }
}
