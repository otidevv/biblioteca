<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Dewey;

class DeweyController extends Controller
{
    //
    public function dewey_buscar(Request $request)
    {
        $q = $request->get('q');

        $deweys = Dewey::where('nombre', 'like', "%{$q}%")
                        ->limit(10)
                        ->get();

        // Retornamos en formato JSON para Select2
        return response()->json($deweys->map(function($d){
            return [
                'id' => $d->id,
                'codigo' => $d->codigo,
                'nombre' => $d->nombre
            ];
        }));


    }
    //aprende de las actualizaciones
    public function aprender($titulo, $codigoDewey)
    {
        $stopwords = ['de','la','el','y','en','los','las','un','una'];
        
        $titulo = strtolower(preg_replace('/[^a-z0-9\s]/', '', $titulo));
        $palabras = array_filter(explode(' ', $titulo), function($p) use ($stopwords) {
            return !in_array($p, $stopwords) && strlen($p) > 2;
        });

        foreach ($palabras as $palabra) {

            DB::table('dewey_aprendizaje')->updateOrInsert(
                ['palabra' => $palabra, 'codigo_dewey' => $codigoDewey],
                ['peso' => DB::raw('peso + 1')]
            );
        }
    }
}
