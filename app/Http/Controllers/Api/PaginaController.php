<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;
use App\Models\Autor;
use App\Models\Materia;
use App\Models\Editorial;
use App\Models\Idioma;
use App\Models\Ejemplar;
use App\Models\Tipo_registro;
use App\Models\Comentario;
use App\Models\Libro;

class PaginaController extends Controller
{
    // metodos para select2 
    // ================== ENDPOINTS PARA SELECT2 ==================

    private function aplicarContextoCatalogo($query, Request $request): void
    {
        $titulo    = $request->filled('titulo')    ? trim((string) $request->titulo)    : null;
        $codigoAnt = $request->filled('codigo_ant') ? trim((string) $request->codigo_ant) : null;

        if ($titulo || $codigoAnt) {
            $query->whereHas('libros', function ($libros) use ($titulo, $codigoAnt) {
                if ($titulo) {
                    $libros->where(function ($q) use ($titulo) {
                        $q->where('titulo', 'like', "%$titulo%")
                          ->orWhere('palabras_clave', 'like', "%$titulo%")
                          ->orWhere('isbn', 'like', "%$titulo%");
                    });
                }
                if ($codigoAnt) {
                    $libros->where('codigo_ant', 'like', "%$codigoAnt%");
                }
                $libros->whereHas('ejemplares', fn($ej) => $ej->whereNotNull('biblioteca_id'));
            });
        } else {
            $query->whereHas('libros', fn($libros) => $libros->whereHas('ejemplares', fn($ej) => $ej->whereNotNull('biblioteca_id')));
        }
    }

    public function listarAutores(Request $request)
    {
        $q     = $request->filled('q') ? $request->get('q') : null;
        $letra = ($request->filled('letra') && $request->letra !== 'todos')
            ? strtoupper(substr((string) $request->letra, 0, 1))
            : null;

        $autores = Autor::query()
            ->when($q, function($query) use ($q) {
                $query->where(function($sub) use ($q) {
                    $sub->where('nombres', 'like', "%$q%")
                        ->orWhere('apellidos', 'like', "%$q%")
                        ->orWhereRaw("CONCAT(nombres, ' ', apellidos) LIKE ?", ["%$q%"])
                        ->orWhereRaw("CONCAT(apellidos, ' ', nombres) LIKE ?", ["%$q%"]);
                });
            })
            ->when($letra, fn($query) => $query->where(function ($sub) use ($letra) {
                $sub->where('apellidos', 'like', "$letra%")
                    ->orWhere('nombres', 'like', "$letra%");
            }))
            ->orderBy('apellidos');

        $this->aplicarContextoCatalogo($autores, $request);

        $limite  = $letra ? 50 : 20;
        $autores = $autores->limit($limite)->get();

        return response()->json(
            $autores->map(fn($a) => [
                'id'   => $a->id,
                'text' => trim($a->nombres . ' ' . $a->apellidos),
            ])
        );
    }

    public function listarMaterias(Request $request)
    {
        $q = $request->get('q');

        $materias = Materia::query()
            ->when($q, fn($query) => $query->where('nombre', 'like', "%$q%"));

        $this->aplicarContextoCatalogo($materias, $request);

        $materias = $materias->limit(20)->get();

        return response()->json(
            $materias->map(fn($m) => [
                'id' => $m->id,
                'text' => $m->nombre,
            ])
        );
    }

    public function listarIdiomas(Request $request)
    {
        $q = $request->get('q');

        $idiomas = Idioma::query()
            ->when($q, fn($query) => $query->where('nombre', 'like', "%$q%"));

        $this->aplicarContextoCatalogo($idiomas, $request);

        $idiomas = $idiomas->limit(20)->get();

        return response()->json(
            $idiomas->map(fn($i) => [
                'id' => $i->id,
                'text' => $i->nombre,
            ])
        );
    }

    public function listarRegistros(Request $request)
    {
        $q = $request->get('q');
        $registros = Tipo_registro::query()
            ->when($q, fn($query) => $query->where('nombre','like',"%$q%"))
            ->limit(20)
            ->get();

        return response()->json(
            $registros->map(fn($r) => [
                'id' => $r->id,
                'text' => $r->nombre,
            ])
        );
    }
        
    public function agregarComentario(Request $request)
    {
        if (! auth()->check()) {
            return response()->json([
                'error' => 'Debes iniciar sesión',
            ], 401);
        }

        $request->validate([
            'libro_id' => 'required|exists:libros,id',
            'comentario' => 'required|string|max:2000',
            'rating' => 'required|integer|min:1|max:5'
        ]);

        Comentario::updateOrCreate(
            ['libro_id' => $request->libro_id, 'user_id' => auth()->id()],
            ['comentario' => $request->comentario, 'calificacion' => $request->rating]
        );

        $comentarios = Comentario::with('usuario')
            ->where('libro_id', $request->libro_id)
            ->latest()
            ->get();

        $libro = Libro::query()
            ->withAvg('comentarios as rating_promedio', 'calificacion')
            ->withCount('comentarios')
            ->findOrFail($request->libro_id);

        return response()->json([
            'ok' => true,
            'comentariosHtml' => view('pagina._comentarios', compact('comentarios'))->render(),
            'ratingHtml' => view('pagina._rating_summary', [
                'libro' => $libro,
                'ratingSize' => '1rem',
            ])->render(),
            'mainRatingHtml' => view('pagina._rating_summary', [
                'libro' => $libro,
                'ratingClass' => 'book-main-rating-stars',
                'ratingSize' => '1rem',
            ])->render(),
        ]);
    }
    public function eliminarComentario($id)
    {
        $comentario = Comentario::findOrFail($id);

        if ($comentario->user_id !== auth()->id()) {
            return response()->json(['error' => 'No tienes permiso para eliminar este comentario.'], 403);
        }

        $libroId = $comentario->libro_id;
        $comentario->delete();

        $comentarios = Comentario::with('usuario')->where('libro_id', $libroId)->latest()->get();
        $libro = Libro::withAvg('comentarios as rating_promedio', 'calificacion')->withCount('comentarios')->findOrFail($libroId);

        return response()->json([
            'ok' => true,
            'comentariosHtml' => view('pagina._comentarios', compact('comentarios'))->render(),
            'ratingHtml'      => view('pagina._rating_summary', ['libro' => $libro, 'ratingSize' => '1rem'])->render(),
            'mainRatingHtml'  => view('pagina._rating_summary', ['libro' => $libro, 'ratingClass' => 'book-main-rating-stars', 'ratingSize' => '1rem'])->render(),
        ]);
    }

    public function actualizarComentario(Request $request, $id)
    {
        $comentario = Comentario::findOrFail($id);

        if ($comentario->user_id !== auth()->id()) {
            return response()->json(['error' => 'No tienes permiso para editar este comentario.'], 403);
        }

        $request->validate([
            'comentario' => 'required|string|max:2000',
            'rating'     => 'required|integer|min:1|max:5',
        ]);

        $comentario->update([
            'comentario'  => $request->comentario,
            'calificacion' => $request->rating,
        ]);

        $libroId = $comentario->libro_id;
        $comentarios = Comentario::with('usuario')->where('libro_id', $libroId)->latest()->get();
        $libro = Libro::withAvg('comentarios as rating_promedio', 'calificacion')->withCount('comentarios')->findOrFail($libroId);

        return response()->json([
            'ok' => true,
            'comentariosHtml' => view('pagina._comentarios', compact('comentarios'))->render(),
            'ratingHtml'      => view('pagina._rating_summary', ['libro' => $libro, 'ratingSize' => '1rem'])->render(),
            'mainRatingHtml'  => view('pagina._rating_summary', ['libro' => $libro, 'ratingClass' => 'book-main-rating-stars', 'ratingSize' => '1rem'])->render(),
        ]);
    }

    public function listarLibros(Request $request)
    {
        $q = $request->get('q');
        $registros = Tipo_registro::query()
            ->when($q, fn($query) => $query->where('nombre','like',"%$q%"))
            ->limit(20)
            ->get();

        return response()->json(
            $registros->map(fn($r) => [
                'id' => $r->id,
                'text' => $r->nombre,
            ])
        );
    } 
public function ejemplarBiblioteca(Request $request, $biblioteca_id)
{       
    $libro_id = $request->libro_id;

    $ejemplares = Ejemplar::where('biblioteca_id', $biblioteca_id)
        ->where('libro_id', $libro_id)
        ->where('estado', Ejemplar::ESTADO_DISPONIBLE)
        ->where('estado_traslado', Ejemplar::TRASLADO_NINGUNO)
        ->select(
            'id',
            DB::raw("
                CONCAT(
                    COALESCE(codigo_ant, ''),
                    COALESCE(tipo, ''),
                    COALESCE(codigo_interno, '')
                ) as codigo
            ")
        )
        ->get();

    return response()->json($ejemplares);
}
//disponibilidad
public function disponibilidad($id)
{
    $libro = Libro::with([
        'ejemplares' => function ($query) {
            $query->where('estado_traslado', Ejemplar::TRASLADO_NINGUNO);
        },
        'ejemplares.biblioteca',
    ])->findOrFail($id);

    return view('pagina._disponibilidad', compact('libro'))->render();
}
public function ejemplares($id)
{
    $libro = Libro::with([
        'ejemplares' => function ($query) {
            $query->where('estado_traslado', Ejemplar::TRASLADO_NINGUNO);
        },
        'ejemplares.biblioteca',
    ])->findOrFail($id);

    return view('pagina._ejemplares', compact('libro'))->render();
}

public function rating($id)
{
    $libro = Libro::query()
        ->withAvg('comentarios as rating_promedio', 'calificacion')
        ->withCount('comentarios')
        ->findOrFail($id);

    return view('pagina._rating_summary', compact('libro'))->render();
}
    
}
