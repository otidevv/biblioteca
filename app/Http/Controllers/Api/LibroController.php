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
}
