<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Rol;
use App\Models\Permiso;
use App\Models\Biblioteca;
use App\Models\Pais;
use App\Models\Libro;
use App\Models\Tipo_registro;
use App\Models\Idioma;
use App\Models\Dewey;
class AdministracionController extends Controller
{
    public function inicio()
    {
        return view('administracion.index');
    }
    public function index(string $modulo, $id=null)
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
            'editoriales' => $this->editoriales(),
            'tipo_registros' => $this->tipo_registros(),
            'autores' => $this->autores(),
            'compras' => $this->compras(),
            'libros' => $this->libros(),
            'libros_nuevo' => $this->libros_nuevo(),
            'libros_editar' => $this->libros_editar($id),
            'ejemplares'=>$this->ejemplares($id),
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
    protected function editoriales()
    {
        $paises = Pais::latest()->get();
        return view('administracion.editorial',compact('paises'));
    }
    protected function tipo_registros()
    {
        return view('administracion.tipo_registro');
    }
    protected function autores()    
    {
        $paises = Pais::latest()->get();
        return view('administracion.autor',compact('paises'));    
    }
    protected function libros()
    {
        return view('administracion.libros');
    }
    protected function ejemplares($id)
    {
        $libro=Libro::with(['autores','tipo_registro','editorial'])
                    ->withCount('ejemplares')->find($id);
        $bibliotecas=Biblioteca::get();
        return view('administracion.ejemplares' ,compact('id','libro','bibliotecas'));
    }   
    protected function libros_nuevo()
    {
        $tipo_registros = Tipo_registro::latest()->get();
        $paises = Pais::latest()->get();
        $idiomas = Idioma::latest()->get();
        $deweys = Dewey::latest()->get();
        return view('administracion.libros_nuevo', compact('tipo_registros','idiomas','paises','deweys'));
    }
    protected function libros_editar($id)
    {
        $tipo_registros = Tipo_registro::latest()->get();
        $libro = Libro::with(['autores','tipo_registro','materias','editorial'])->find($id);
        $paises = Pais::latest()->get();
        $idiomas = Idioma::latest()->get();
        $deweys = Dewey::latest()->get();
        return view('administracion.libros_nuevo', compact('tipo_registros','idiomas','paises','deweys','libro'));
    }
    protected function compras()    
    {
        return view('inventario.compras');    
    }
}   