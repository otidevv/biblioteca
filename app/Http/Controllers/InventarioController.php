<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Proveedor;
use App\Models\Editorial;
use App\Models\Libro;
use App\Models\Tipo_registro;
use App\Models\Idioma;
use App\Models\Pais;
use App\Models\Dewey;
class InventarioController extends Controller
{
    //
    public function index(string $modulo)
    {
        return match ($modulo) {

            // 👉 USUARIOS
            'compras' => $this->compras(),
            'compra_nuevo' => $this->compra_nuevo(),
            'libros' => $this->libros(),
            'libros_nuevo' => $this->libros_nuevo(),
            default => abort(404),
        };
    }

    protected function compras()
    {
        return view('inventario.compras');
    }
    protected function compra_nuevo()
    {
        $proveedores = Proveedor::latest()->get();
        $editoriales = Editorial::latest()->get();
        $libros = Libro::with(['autor','editorial'])->get();
        return view('inventario.compra_nuevo', compact('proveedores', 'editoriales','libros'));
    }
    protected function libros()
    {
        return view('inventario.libros');
    }   
    protected function libros_nuevo()
    {
        $tipo_registros = Tipo_registro::latest()->get();
        $paises = Pais::latest()->get();
        $idiomas = Idioma::latest()->get();
        $deweys = Dewey::latest()->get();
        return view('inventario.libros_nuevo', compact('tipo_registros','idiomas','paises','deweys'));
    }
}
