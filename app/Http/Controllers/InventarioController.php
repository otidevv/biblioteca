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
}
