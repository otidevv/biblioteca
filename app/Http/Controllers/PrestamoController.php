<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PrestamoController extends Controller
{
    //
    public function index(string $modulo)
    {
        return match ($modulo) {

            // 👉 USUARIOS
            'registro' => $this->registro(),
            'reservas' => $this->reservas(),
            default => abort(404),
        };
    }

    protected function reservas()
    {
        return view('prestamos.reserva');
    }
    protected function registro()
    {
        return view('prestamos.prestamos');
    }
}
