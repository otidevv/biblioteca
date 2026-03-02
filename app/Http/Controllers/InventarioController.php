<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
        $proveedores = \App\Models\Proveedor::latest()->get();
        $editoriales = \App\Models\Editorial::latest()->get();
        return view('inventario.compra_nuevo', compact('proveedores', 'editoriales'));
    }
}
