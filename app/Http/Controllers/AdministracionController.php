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
use App\Models\Prestamo;
use App\Models\Visita;
use App\Models\Ejemplar;
use App\Models\ActividadCategoria;
use App\Services\ReporteInventarioFisicoService;
use Illuminate\Support\Facades\Auth;
class AdministracionController extends Controller
{
    public function inicio()
    {
        $totalLibros      = Libro::count();
        $totalUsuarios    = User::count();
        $prestamosActivos = Prestamo::whereIn('estado', [1, 'prestado'])->count();
        $totalBibliotecas = Biblioteca::count();
        $librosRecientes  = Libro::with(['autores', 'editorial'])->latest()->limit(5)->get();

        $visitasHoy    = Visita::whereDate('fecha', today())->count();
        $visitasSemana = Visita::whereBetween('fecha', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $visitasMes    = Visita::whereYear('fecha', now()->year)->whereMonth('fecha', now()->month)->count();
        $visitasTotal  = Visita::count();

        $visitasPorDia = Visita::selectRaw('fecha, COUNT(*) as total')
            ->where('fecha', '>=', now()->subDays(29)->toDateString())
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->pluck('total', 'fecha');

        $bibliotecaAsignada     = null;
        $librosEnMiBiblioteca   = null;
        $bibliotecaId = Auth::user()
            ->usuarioRolBibliotecas()
            ->whereNotNull('biblioteca_id')
            ->value('biblioteca_id');

        if ($bibliotecaId) {
            $bibliotecaAsignada   = Biblioteca::find($bibliotecaId);
            $librosEnMiBiblioteca = Ejemplar::where('biblioteca_id', $bibliotecaId)
                ->distinct('libro_id')
                ->count('libro_id');
        }

        return view('administracion.index', compact(
            'totalLibros',
            'totalUsuarios',
            'prestamosActivos',
            'totalBibliotecas',
            'librosRecientes',
            'visitasHoy',
            'visitasSemana',
            'visitasMes',
            'visitasTotal',
            'visitasPorDia',
            'bibliotecaAsignada',
            'librosEnMiBiblioteca'
        ));
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
            'sanciones' => $this->sanciones(),
            'notificaciones' => $this->notificaciones(),
            'actividades' => $this->actividades(),
            'compras' => $this->compras(),
            'libros' => $this->libros(),
            'traslados_ejemplares' => $this->trasladosEjemplares(),
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
        $bibliotecas  = Biblioteca::withCount('ejemplares')->orderBy('nombre')->get();

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
    protected function sanciones()
    {
        return view('administracion.sanciones');
    }
    protected function notificaciones()
    {
        return view('administracion.notificaciones');
    }
    protected function actividades()
    {
        $categorias = ActividadCategoria::where('estado', 1)->orderBy('nombre')->get();
        return view('administracion.actividades', compact('categorias'));
    }
    protected function libros()
    {
        $bibliotecaUsuarioId = Auth::user()
            ->usuarioRolBibliotecas()
            ->whereNotNull('biblioteca_id')
            ->value('biblioteca_id');

        $bibliotecas = Biblioteca::orderBy('nombre')->get(['id', 'nombre', 'estado']);

        return view('administracion.libros', compact('bibliotecaUsuarioId', 'bibliotecas'));
    }

    public function trasladosEjemplares()
    {
        $service = app(ReporteInventarioFisicoService::class);
        $contexto = $service->resolverContextoBibliotecas(Auth::user());
        $bibliotecas = Biblioteca::query()->orderBy('nombre')->get(['id', 'nombre']);

        return view('administracion.traslados_ejemplares', [
            'bibliotecas' => $bibliotecas,
            'bibliotecaFijaId' => $contexto['bibliotecaFijaId'],
            'puedeFiltrarBiblioteca' => $contexto['puedeFiltrarBiblioteca'],
            'bibliotecasUsuarioIds' => $contexto['bibliotecasAsignadas']->all(),
            'accesoGlobalBibliotecas' => $contexto['accesoGlobal'],
        ]);
    }
    protected function ejemplares($id)
    {
        $libro=Libro::with(['autores','tipo_registro','editorial'])
                    ->withCount([
                        'ejemplares',
                        'ejemplares as disponibles_count' => fn($q) => $q->where('estado', 1),
                        'ejemplares as pendientes_count' => fn($q) => $q->where('estado_traslado', 1),
                    ])->find($id);
        $service = app(ReporteInventarioFisicoService::class);
        $contexto = $service->resolverContextoBibliotecas(Auth::user());

        $bibliotecas = Biblioteca::query()->orderBy('nombre')->get(['id', 'nombre']);

        return view('administracion.ejemplares' ,[
            'id' => $id,
            'libro' => $libro,
            'bibliotecas' => $bibliotecas,
            'bibliotecasDestino' => $bibliotecas,
            'bibliotecaFijaId' => $contexto['bibliotecaFijaId'],
            'puedeFiltrarBiblioteca' => $contexto['puedeFiltrarBiblioteca'],
            'bibliotecasUsuarioIds' => $contexto['bibliotecasAsignadas']->all(),
            'accesoGlobalBibliotecas' => $contexto['accesoGlobal'],
            'disponiblesCount' => $libro->disponibles_count ?? 0,
            'pendientesCount' => $libro->pendientes_count ?? 0,
        ]);
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
        $libro = Libro::with(['autores','tipo_registro','materias','editorial','ejemplares'])->find($id);

        if (!$libro) {
            abort(404);
        }

        $servicio = app(ReporteInventarioFisicoService::class);
        $contexto = $servicio->resolverContextoBibliotecas(Auth::user());

        $puedeEditar = $contexto['accesoGlobal']
            || $libro->ejemplares->whereIn('biblioteca_id', $contexto['bibliotecasAsignadas']->all())->isNotEmpty();

        if (!$puedeEditar) {
            abort(403, 'No tienes permiso para editar este libro.');
        }

        $tipo_registros = Tipo_registro::latest()->get();
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
