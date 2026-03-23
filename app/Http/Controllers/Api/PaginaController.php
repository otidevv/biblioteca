<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Autor;
use App\Models\Materia;
use App\Models\Editorial;
use App\Models\Idioma;
use App\Models\Tipo_registro;
use App\Models\Comentario;
class PaginaController extends Controller
{
    // metodos para select2 
    // ================== ENDPOINTS PARA SELECT2 ==================

    public function listarAutores(Request $request)
    {
        $q = $request->get('q');
        $autores = Autor::query()
            ->when($q, fn($query) => $query->where('nombres','like',"%$q%")->orWhere('apellidos','like',"%$q%"))
            ->limit(20)
            ->get();

        return response()->json(
            $autores->map(fn($a) => [
                'id' => $a->id,
                'text' => $a->nombres.' '.$a->apellidos,
            ])
        );
    }

    public function listarMaterias(Request $request)
    {
        $q = $request->get('q');
        $materias = Materia::query()
            ->when($q, fn($query) => $query->where('nombre','like',"%$q%"))
            ->limit(20)
            ->get();

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
            ->when($q, fn($query) => $query->where('nombre','like',"%$q%"))
            ->limit(20)
            ->get();

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
        $request->validate([
            'libro_id' => 'required',
            'comentario' => 'required',
            'rating' => 'required|integer|min:1|max:5'
        ]);

        Comentario::create([
            'libro_id' => $request->libro_id,
            'user_id' => auth()->id(),
            'comentario' => $request->comentario,
            'calificacion' => $request->rating
        ]);

        $comentarios = Comentario::with('usuario')
            ->where('libro_id', $request->libro_id)
            ->latest()
            ->get();

        return view('pagina._comentarios', compact('comentarios'));
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
}