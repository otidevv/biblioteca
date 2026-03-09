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
}
