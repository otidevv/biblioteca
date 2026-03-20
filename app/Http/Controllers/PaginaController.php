<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dewey;
use App\Models\Libro;
use App\Models\Autor;
use App\Models\Materia;
use App\Models\Idioma;
use App\Models\Tipo_registro;
class PaginaController extends Controller
{
    //    
    public function index(Request $request)
    {

        $bibliotecas = Biblioteca::all();
        $libros = Libro::latest()->take(8)->get();

        return view('home', compact('bibliotecas','libros'));
        /*
            $query = Libro::with(['autores','editorial','materias','idioma','tipo_registro'])
                ->select('id','titulo','imagen');

            // 🔍 Búsqueda general
            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function($q) use ($search) {
                    $q->where('titulo','like',"%{$search}%")
                    ->orWhere('palabras_clave','like',"%{$search}%")
                    ->orWhereHas('autores', function($qa) use ($search) {
                        $qa->where('nombres','like',"%{$search}%")
                            ->orWhere('apellidos','like',"%{$search}%");
                    });
                });
            }

            // 📑 Tipo de registro
            if ($request->filled('registro_id')) {
                $query->where('tipo_registro_id', $request->registro_id);
            }

            // 🌐 Idioma
            if ($request->filled('idioma_id')) {
                $query->where('idioma_id', $request->idioma_id);
            }

            // 👤 Autor
            if ($request->filled('autor_id')) {
                $query->whereHas('autores', function($q) use ($request) {
                    $q->where('id', $request->autor_id);
                });
            }

            // 📚 Materia
            if ($request->filled('materia_id')) {
                $query->where('materia_id', $request->materia_id);
            }

            $libros = $query->paginate(16);

            // Si la petición es AJAX, devolvemos solo el partial
            if ($request->ajax()) {
                return view('pagina._libros', compact('libros'))->render();
            }

            return view('pagina.index', compact('libros'));
        */
    }

    public function showBiblioteca($id)
    {
        $biblioteca = Biblioteca::findOrFail($id);
        $libros = $biblioteca->libros;

        return view('biblioteca', compact('biblioteca','libros'));
    }

    public function showLibro($id)
    {
        $libro = Libro::findOrFail($id);
        return view('libro', compact('libro'));
    }
    public function libro($id)
    {
        // Traer el libro con todas sus relaciones, incluyendo ejemplares y biblioteca
        $libro = Libro::with([
            'autores',
            'editorial',
            'materias',
            'idioma',
            'tipo_registro',
            'comentarios.usuario',
            'ejemplares.biblioteca' // 👈 aquí traes los ejemplares y su biblioteca
        ])->findOrFail($id);

        // Palabras clave
        $keywords = collect(explode(' ', $libro->titulo))
            ->merge(explode(' ', $libro->palabras_clave ?? ''))
            ->filter(fn($word) => strlen($word) > 3)
            ->unique();

        // Libros relacionados
        $relacionados = Libro::with(['autores','editorial','materias','idioma','tipo_registro'])
            ->where('id', '!=', $libro->id)
            ->where(function($q) use ($libro, $keywords) {
                $q->whereHas('materias', function($mq) use ($libro) {
                    $mq->whereIn('materias.id', $libro->materias->pluck('id'));
                });

                if ($keywords->isNotEmpty()) {
                    $q->orWhere(function($sub) use ($keywords) {
                        foreach ($keywords as $word) {
                            $sub->orWhere('titulo','like',"%{$word}%")
                                ->orWhere('palabras_clave','like',"%{$word}%")
                                ->orWhere('palabras_clave','like',"%{$word}%");
                        }
                    });
                }
            })
            ->limit(8)
            ->get();

        return view('pagina.libro', compact('libro','relacionados'));
    }

}
