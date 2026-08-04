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

        if ($request->get('codigoDewey')) {

            $q = $request->get('codigoDewey');
            $deweys = Dewey::where('codigo',$q)
                ->get();
            return response()->json(
                $deweys->map(function ($d) {
                    return [
                        'id' => $d->id,
                        'text' => $d->codigo . ' - ' . $d->nombre // 🔥 IMPORTANTE para Select2
                    ];
                })
            );

        }

        $termino = trim((string) $q);

        $consulta = Dewey::query();

        if ($termino !== '') {
            // Coincide por codigo (prefijo), por el termino completo en el nombre o,
            // si no, porque todas las palabras aparezcan en nombre/keywords.
            // Asi funcionan tanto "800" como "literatura peruana".
            $palabras = preg_split('/\s+/', $termino, -1, PREG_SPLIT_NO_EMPTY) ?: [];

            $consulta->where(function ($grupo) use ($termino, $palabras) {
                $grupo->where('codigo', 'like', "{$termino}%")
                      ->orWhere('nombre', 'like', "%{$termino}%")
                      ->orWhere(function ($todasLasPalabras) use ($palabras) {
                          foreach ($palabras as $palabra) {
                              $todasLasPalabras->where(function ($campo) use ($palabra) {
                                  $campo->where('nombre', 'like', "%{$palabra}%")
                                        ->orWhere('keywords', 'like', "%{$palabra}%");
                              });
                          }
                      });
            });
        }

        $deweys = $consulta->orderBy('codigo')->limit(30)->get();

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
