<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Rol;
use App\Models\Permiso;
use App\Models\Biblioteca; 
use App\Models\Carrera;

class LectoresController extends Controller
{
    //
    public function index(string $modulo)
    {
        return match ($modulo) {

            // 👉 USUARIOS
            'registro' => $this->lectores(),
            // 👉 ROLES Y PERMISOS
            'prestamos' =>  $this->prestamos(),
            // 👉 MULTAS
            'multas' =>  $this->multas(),
            // 👉 BACKUPS
            'importaciones' => $this->importaciones(),
            default => abort(404),
        };
    }

    protected function lectores()
    {
        $carreras=Carrera::latest()->get();
        return view('lectores.registro_lectores', compact('carreras'));
    }
    protected function prestamos()
    {
        $usuarios = User::latest()->get();
        $tiposUsuarios  = Rol::latest()->get();
        $bibliotecas  = Biblioteca::latest()->get();

        return view('administracion.usuario', compact('usuarios', 'tiposUsuarios', 'bibliotecas'));
    }
    protected function multas()
    {
        $permisos = Permiso::whereNull('permiso_id')
            ->with('hijos')
            ->get();

        return view('administracion.roles_permisos', compact('permisos'));
    }
    protected function importaciones()
    {
        return view('administracion.biblioteca');
    }
}
