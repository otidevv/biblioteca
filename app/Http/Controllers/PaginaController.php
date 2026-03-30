<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dewey;
use App\Models\Libro;
use App\Models\Autor;
use App\Models\Materia;
use App\Models\Idioma;
use App\Models\Editorial;
use App\Models\Biblioteca;
use App\Models\Reservacion;
use App\Models\Prestamo;
use App\Models\Actividad;
use App\Models\ActividadCategoria;
use App\Models\Tipo_registro;
use Illuminate\Support\Facades\Schema;
class PaginaController extends Controller
{
    //    
    public function index(Request $request)
    {

        $bibliotecas = Biblioteca::all();
        $libros = Libro::with(['autores', 'editorial'])
            ->withAvg('comentarios as rating_promedio', 'calificacion')
            ->withCount('comentarios')
            ->latest()
            ->take(8)
            ->get();
        $actividades = Actividad::with('categoria')
            ->where('estado', 1)
            ->where(function ($query) {
                $query->whereNull('fecha_fin')
                    ->orWhereDate('fecha_fin', '>=', now()->toDateString());
            })
            ->orderBy('fecha_inicio')
            ->take(4)
            ->get();

        return view('pagina.index', compact('bibliotecas','libros', 'actividades'));
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
    public function catalogo(Request $request)
    {
        $query = Libro::with(['autores','editorial'])
            ->withAvg('comentarios as rating_promedio', 'calificacion')
            ->withCount('comentarios')
            ->whereHas('ejemplares', function ($q) {
                $q->whereNotNull('biblioteca_id');
            });

        if ($request->titulo) {
            $query->where('titulo','like','%'.$request->titulo.'%');
        }

        if ($request->autor_id) {
            $query->whereHas('autores', function($q) use ($request){
                $q->where('autores.id', $request->autor_id);
            });
        }

        if ($request->editorial_id) {
            $query->where('editorial_id',$request->editorial_id);
        }

        if ($request->materia) {
            $query->where('materia',$request->materia);
        }

        $libros = $query->paginate(8)->withQueryString();

        // 🔥 SI ES AJAX → SOLO DEVUELVE LA LISTA
        if ($request->ajax()) {
            return view('pagina._libros', compact('libros'))->render();
        }


        return view('pagina.catalogo', compact('libros'));
    }


    public function showBiblioteca(Request $request,$id)
    {
        $biblioteca = Biblioteca::findOrFail($id);
        
        $query = Libro::with(['autores','editorial','ejemplares'])
            ->withAvg('comentarios as rating_promedio', 'calificacion')
            ->withCount('comentarios')
            ->whereHas('ejemplares', function($q) use ($id) {
                $q->where('biblioteca_id', $id);
            });

        if ($request->titulo) {
            $query->where('titulo','like','%'.$request->titulo.'%');
        }

        if ($request->autor_id) {
            $query->whereHas('autores', function($q) use ($request){
                $q->where('autores.id', $request->autor_id);
            });
        }

        if ($request->editorial_id) {
            $query->where('editorial_id',$request->editorial_id);
        }

        if ($request->materia) {
            $query->where('materia',$request->materia);
        }

        $libros = $query->paginate(8)->withQueryString();

        // 🔥 SI ES AJAX → SOLO DEVUELVE LA LISTA
        if ($request->ajax()) {
            return view('pagina._libros', compact('libros'))->render();
        }


        return view('pagina.catalogo', compact('libros','biblioteca'));
    }

    public function showLibro($id)
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
        ])
        ->withAvg('comentarios as rating_promedio', 'calificacion')
        ->withCount('comentarios')
        ->findOrFail($id);
        //OBTENER BIBLIOTECAS QUE TIENEN ESE LIBRO
        $bibliotecas = Biblioteca::whereHas('ejemplares', function($q) use ($id) {
            $q->where('libro_id', $id)
            ->where('estado', 1); // solo disponibles
        })->get();
        // Palabras clave
        $keywords = collect(explode(' ', $libro->titulo))
            ->merge(explode(' ', $libro->palabras_clave ?? ''))
            ->filter(fn($word) => strlen($word) > 3)
            ->unique();

        // Libros relacionados
        $libros = Libro::with(['autores','editorial','materias','idioma','tipo_registro'])
            ->withAvg('comentarios as rating_promedio', 'calificacion')
            ->withCount('comentarios')
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
            ->limit(4)
            ->get();

        return view('pagina.libro', compact('libro','libros','bibliotecas'));
    }
    public function misReservas()
    {        
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $reservas = Reservacion::with(['ejemplar.libro', 'ejemplar.biblioteca'])
            ->where('lector_id', auth()->id())
            ->latest()
            ->get();

        return view('pagina.mis_reservas', compact('reservas'));
    }

    public function misPrestamos()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $prestamos = Prestamo::with(['ejemplar.libro', 'ejemplar.biblioteca'])
            ->where('lector_id', auth()->id())
            ->latest('fecha_prestamo')
            ->get();

        return view('pagina.mis_prestamos', compact('prestamos'));
    }

    public function eventos()
    {
        $eventosQuery = Actividad::with('categoria')
            ->where('estado', 1);

        if (Schema::hasColumn('actividades', 'destacado')) {
            $eventosQuery->orderByDesc('destacado');
        }

        $eventosDestacados = $eventosQuery
            ->orderBy('fecha_inicio')
            ->limit(6)
            ->get();

        $categorias = ActividadCategoria::withCount(['actividades' => function ($query) {
                $query->where('estado', 1);
            }])
            ->where('estado', 1)
            ->orderBy('nombre')
            ->get();

        $agenda = Actividad::with('categoria')
            ->where('estado', 1)
            ->orderBy('fecha_inicio')
            ->limit(6)
            ->get();

        return view('pagina.eventos', compact('eventosDestacados', 'agenda', 'categorias'));
    }

}
