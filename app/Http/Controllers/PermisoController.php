<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Proveedor;
use App\Models\Editorial;
use App\Models\Libro;
use App\Models\Tipo_registro;
use App\Models\Idioma;
use App\Models\Pais;
use App\Models\Reservacion;

class PermisoController extends Controller
{
    //
    public function index(string $modulo)
    {
        return match ($modulo) {

            // 👉 USUARIOS
            'reservas' => $this->reservas(),
            default => abort(404),
        };
    }

    protected function reservas()
    {
        return view('inventario.compras');
    }
}
