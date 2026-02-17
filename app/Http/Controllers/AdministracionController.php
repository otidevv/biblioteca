<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Rol;
class AdministracionController extends Controller
{
    public function index(string $modulo)
    {
        return match ($modulo) {

            // 👉 USUARIOS
            'usuarios' => $this->usuarios(),
            // 👉 ROLES Y PERMISOS
            'roles_permisos' => view('administracion.roles_permisos'),
            // 👉 BACKUPS
            'backups' => view('administracion.backups.index'),
            default => abort(404),
        };
    }

    protected function usuarios()
    {
        $usuarios = User::latest()->get();
        $tiposUsuarios  = Rol::latest()->get();

        return view('administracion.usuario', compact('usuarios', 'tiposUsuarios'));
    }
    protected function roles_permisos()
    {
        $tiposUsuarios = Rol::withCount('users')->latest()->get();

        return view('administracion.roles_permisos', compact('usuarios', 'tiposUsuarios'));
    }
    protected function bibliotecas()
    {
        return view('administracion.biblioteca');
    }
}   