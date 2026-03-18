<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dewey;
use App\Models\Libro;
class PaginaController extends Controller
{
    //    
    public function index()
    {
        $libros = Libro::with('autores')
            ->select('id','titulo','imagen') // optimiza consulta
            ->paginate(20);
        return view('pagina.index', compact('libros'));
    }
    public function libro()
    {
        $libros = Libro::with('autores')
            ->select('id','titulo','imagen') // optimiza consulta
            ->paginate(20);
        return view('pagina.libro', compact('libros'));
    }
}
