<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Rol;
use App\Models\Permiso;
use App\Models\Biblioteca;
class AdministracionController extends Controller
{
    public function index(string $modulo)
    {
        return match ($modulo) {

            // 👉 USUARIOS
            'usuarios' => $this->usuarios(),
            // 👉 ROLES Y PERMISOS
            'roles_permisos' =>  $this->roles_permisos(),
            // 👉 BACKUPS
            'backups' => view('administracion.backups.index'),
            'bibliotecas' => $this->bibliotecas(),
            'proveedores' => $this->proveedores(),
            default => abort(404),
        };
    }

    protected function usuarios()
    {
        $usuarios = User::latest()->get();
        $tiposUsuarios  = Rol::latest()->get();
        $bibliotecas  = Biblioteca::latest()->get();

        return view('administracion.usuario', compact('usuarios', 'tiposUsuarios', 'bibliotecas'));
    }
    protected function roles_permisos()
    {
        $permisos = Permiso::whereNull('permiso_id')
            ->with('hijos')
            ->get();

        return view('administracion.roles_permisos', compact('permisos'));
    }
    protected function bibliotecas()
    {
        return view('administracion.biblioteca');
    }
    protected function proveedores()
    {
        return view('administracion.proveedor');
    }
}   