<?php

namespace App\Services;

use App\Models\Actividad;
use App\Models\Biblioteca;
use App\Models\Libro;
use App\Models\Prestamo;
use App\Models\Reservacion;
use App\Models\Sancion;
use App\Models\User;
use App\Models\Visita;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Indicadores del panel de administracion. Todo se calcula sobre las bibliotecas
 * que el usuario puede ver: su sede asignada o, con acceso global, la red completa
 * (o la biblioteca que elija en el filtro).
 */
class DashboardAdminService
{
    const DIAS_SERIE = 30;
    const DIAS_SEMANA = 90;

    // Numeracion de DAYOFWEEK() en MySQL: 1 = domingo.
    const DIAS_CORTOS = [2 => 'Lun', 3 => 'Mar', 4 => 'Mié', 5 => 'Jue', 6 => 'Vie', 7 => 'Sáb', 1 => 'Dom'];
    const DIAS_LARGOS = [2 => 'lunes', 3 => 'martes', 4 => 'miércoles', 5 => 'jueves', 6 => 'viernes', 7 => 'sábado', 1 => 'domingo'];

    public function __construct(private ReporteInventarioFisicoService $inventario)
    {
    }

    public function resumen(User $user, $bibliotecaSolicitada = null): array
    {
        $alcance = $this->alcance($user, $bibliotecaSolicitada);
        $ids = $alcance['ids'];
        $codigos = $this->codigosPermiso($user);

        $activos = $this->prestamosActivos($ids);

        return [
            'usuario' => $user,
            'saludo' => $this->saludo(),
            'fechaLarga' => Str::ucfirst(now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY')),
            'alcance' => $alcance,
            'sala' => $this->estadoSala($activos),
            'kpis' => $this->kpis($ids, $activos),
            'circulacion' => $this->circulacion($ids),
            'atencion' => $this->atencion($ids, $activos),
            'librosTop' => $this->librosMasSolicitados($ids),
            'carreras' => $this->carreras($ids),
            'semana' => $this->demandaPorDia($ids),
            'coleccion' => $this->coleccion($ids),
            'movimientos' => $this->movimientosRecientes($ids),
            'bibliotecas' => $this->comparativoBibliotecas($alcance, $activos),
            'visitas' => $this->visitas(),
            'agenda' => Actividad::with('categoria')->vigentes()->orderBy('fecha_inicio')->limit(3)->get(),
            'acciones' => $this->accionesPrincipales($codigos),
            'accesos' => $this->accesos($codigos),
            'puede' => fn (string $codigo) => $codigos->contains($codigo),
        ];
    }

    /**
     * ids = null significa toda la red; [] significa que no tiene bibliotecas asignadas.
     */
    public function alcance(User $user, $bibliotecaSolicitada = null): array
    {
        $contexto = $this->inventario->resolverContextoBibliotecas($user);
        $visibles = $this->inventario->obtenerBibliotecasVisibles($user);
        $solicitada = filter_var($bibliotecaSolicitada, FILTER_VALIDATE_INT) ?: null;

        if ($solicitada && ! $visibles->contains('id', $solicitada)) {
            $solicitada = null;
        }

        if ($contexto['accesoGlobal']) {
            $ids = $solicitada ? [$solicitada] : null;
        } else {
            $asignadas = $contexto['bibliotecasAsignadas']->all();
            $ids = $solicitada ? [$solicitada] : $asignadas;
        }

        $nombre = match (true) {
            $ids === null => 'Todas las bibliotecas',
            count($ids) === 1 => self::nombrePropio(optional($visibles->firstWhere('id', $ids[0]))->nombre) ?: 'Biblioteca asignada',
            count($ids) === 0 => 'Sin biblioteca asignada',
            default => count($ids) . ' bibliotecas asignadas',
        };

        return [
            'ids' => $ids,
            'nombre' => $nombre,
            'seleccionada' => $solicitada,
            'visibles' => $visibles,
            'puedeFiltrar' => $contexto['puedeFiltrarBiblioteca'] && $visibles->count() > 1,
            'global' => $contexto['accesoGlobal'],
        ];
    }

    // Codigos de permiso efectivos: los asignados y, para un modulo padre, todos sus hijos.
    public function codigosPermiso(User $user): Collection
    {
        return $user->roles()
            ->where(fn ($q) => $q->whereNull('usuario_rol_bibliotecas.estado')->orWhere('usuario_rol_bibliotecas.estado', '!=', 0))
            ->with('permisos.hijos')
            ->get()
            ->pluck('permisos')
            ->flatten()
            ->flatMap(fn ($permiso) => collect([$permiso->codigo])->merge($permiso->hijos->pluck('codigo')))
            ->unique()
            ->values();
    }

    // "INGENIERIA DE SISTEMAS E INFORMATICA" -> "Ingenieria de Sistemas e Informatica"
    public static function nombrePropio(?string $texto): string
    {
        $menores = ['de', 'del', 'la', 'las', 'los', 'y', 'e', 'en', 'a', 'el', 'para', 'por'];

        return collect(preg_split('/\s+/', Str::lower(trim((string) $texto))))
            ->filter(fn ($palabra) => $palabra !== '')
            ->values()
            ->map(fn ($palabra, $i) => $i > 0 && in_array($palabra, $menores, true) ? $palabra : Str::ucfirst($palabra))
            ->implode(' ');
    }

    protected function saludo(): string
    {
        $hora = now()->hour;

        return $hora < 12 ? 'Buenos días' : ($hora < 19 ? 'Buenas tardes' : 'Buenas noches');
    }

    // ---------------------------------------------------------------- consultas base

    protected function prestamosQuery(?array $ids): Builder
    {
        $query = DB::table('prestamos as p')->join('ejemplares as e', 'e.id', '=', 'p.ejemplar_id');

        if ($ids !== null) {
            $query->whereIn('e.biblioteca_id', $ids);
        }

        return $query;
    }

    protected function enBibliotecas($query, ?array $ids)
    {
        return $query->when($ids !== null, fn ($q) => $q->whereHas('ejemplar', fn ($e) => $e->whereIn('biblioteca_id', $ids)));
    }

    protected function prestamosActivos(?array $ids): Collection
    {
        return $this->enBibliotecas(Prestamo::query(), $ids)
            ->with(['ejemplar:id,libro_id,biblioteca_id,codigo_interno', 'ejemplar.libro:id,titulo', 'ejemplar.biblioteca:id,nombre', 'lector:id,name'])
            ->where('estado', 1)
            ->get();
    }

    protected function reservasPendientes(?array $ids)
    {
        return $this->enBibliotecas(Reservacion::query(), $ids)
            ->where('estado', 0)
            ->whereRaw("TIMESTAMP(fecha_limite, '20:00:00') > ?", [now()->format('Y-m-d H:i:s')]);
    }

    // ---------------------------------------------------------------- indicadores

    protected function estadoSala(Collection $activos): array
    {
        $cierre = Prestamo::cierreDeHoy();
        $abierta = now()->lt($cierre);
        $enSala = $activos->where('prestamo_lugar', Prestamo::EN_SALA)->count();

        return [
            'abierta' => $abierta,
            'cierre' => $cierre->format('H:i'),
            'restante' => ! $abierta ? null : (now()->diffInMinutes($cierre) < 1
                ? 'menos de un minuto'
                : now()->locale('es')->diffForHumans($cierre, ['syntax' => Carbon::DIFF_ABSOLUTE, 'parts' => 2, 'minimumUnit' => 'minute'])),
            'en_sala' => $enSala,
        ];
    }

    protected function kpis(?array $ids, Collection $activos): array
    {
        $hoy = today();
        $semanaPasada = $hoy->copy()->subWeek();

        $prestamosHoy = $this->prestamosQuery($ids)->whereDate('p.fecha_prestamo', $hoy)
            ->selectRaw('COUNT(*) total, SUM(p.prestamo_lugar = 0) sala, SUM(p.prestamo_lugar = 1) casa, SUM(p.es_libre = 1) libres')
            ->first();
        $prestamosSemanaPasada = $this->prestamosQuery($ids)->whereDate('p.fecha_prestamo', $semanaPasada)->count();

        $devolucionesHoy = $this->prestamosQuery($ids)->whereDate('p.fecha_devolucion', $hoy)
            ->selectRaw('COUNT(*) total, SUM(p.estado_prestamo = 2) tardanza, SUM(p.estado_prestamo = 3) deterioro')
            ->first();
        $devolucionesSemanaPasada = $this->prestamosQuery($ids)->whereDate('p.fecha_devolucion', $semanaPasada)->count();

        $vencidos = $activos->filter(fn (Prestamo $p) => $p->fecha_limite_real && now()->gte($p->fecha_limite_real));
        $masAntiguo = $vencidos->min(fn (Prestamo $p) => $p->fecha_limite_real);

        $reservas = $this->reservasPendientes($ids)->get(['id', 'fecha_limite']);

        $sanciones = Sancion::query()
            ->where('estado', 1)
            ->whereDate('fecha_fin', '>=', $hoy->toDateString())
            ->when($ids !== null, fn ($q) => $q->where(fn ($w) => $w->whereNull('biblioteca_id')->orWhereIn('biblioteca_id', $ids)));
        $sancionesMes = Sancion::query()
            ->where('created_at', '>=', $hoy->copy()->startOfMonth())
            ->when($ids !== null, fn ($q) => $q->whereIn('biblioteca_id', $ids))
            ->count();

        return [
            'prestamos_hoy' => [
                'valor' => (int) $prestamosHoy->total,
                'sala' => (int) $prestamosHoy->sala,
                'casa' => (int) $prestamosHoy->casa,
                'libres' => (int) $prestamosHoy->libres,
                'delta' => (int) $prestamosHoy->total - $prestamosSemanaPasada,
                'referencia' => $prestamosSemanaPasada,
            ],
            'devoluciones_hoy' => [
                'valor' => (int) $devolucionesHoy->total,
                'tardanza' => (int) $devolucionesHoy->tardanza,
                'deterioro' => (int) $devolucionesHoy->deterioro,
                'delta' => (int) $devolucionesHoy->total - $devolucionesSemanaPasada,
                'referencia' => $devolucionesSemanaPasada,
            ],
            'en_circulacion' => [
                'valor' => $activos->count(),
                'sala' => $activos->where('prestamo_lugar', Prestamo::EN_SALA)->count(),
                'casa' => $activos->where('prestamo_lugar', Prestamo::A_CASA)->count(),
            ],
            'fuera_de_plazo' => [
                'valor' => $vencidos->count(),
                'mas_antiguo' => $masAntiguo ? $masAntiguo->locale('es')->diffForHumans(['parts' => 1]) : null,
            ],
            'reservas' => [
                'valor' => $reservas->count(),
                'vencen_hoy' => $reservas->filter(fn ($r) => $r->fecha_limite && $r->fecha_limite->isToday())->count(),
            ],
            'sanciones' => [
                'valor' => $sanciones->count(),
                'mes' => $sancionesMes,
            ],
        ];
    }

    protected function circulacion(?array $ids): array
    {
        $desde = today()->subDays(self::DIAS_SERIE - 1);

        $filas = $this->prestamosQuery($ids)
            ->whereDate('p.fecha_prestamo', '>=', $desde->toDateString())
            ->selectRaw('DATE(p.fecha_prestamo) dia, p.prestamo_lugar lugar, COUNT(*) total')
            ->groupBy('dia', 'lugar')
            ->get();

        $dias = [];
        for ($i = 0; $i < self::DIAS_SERIE; $i++) {
            $fecha = $desde->copy()->addDays($i);
            $clave = $fecha->toDateString();
            $dias[] = [
                'fecha' => $clave,
                'etiqueta' => $fecha->locale('es')->isoFormat('D MMM'),
                'etiqueta_larga' => Str::ucfirst($fecha->locale('es')->isoFormat('ddd D [de] MMMM')),
                'sala' => (int) $filas->where('dia', $clave)->where('lugar', Prestamo::EN_SALA)->sum('total'),
                'casa' => (int) $filas->where('dia', $clave)->where('lugar', Prestamo::A_CASA)->sum('total'),
            ];
        }

        $total = collect($dias)->sum(fn ($d) => $d['sala'] + $d['casa']);
        $diasConAtencion = collect($dias)->filter(fn ($d) => $d['sala'] + $d['casa'] > 0)->count();

        $lectores = $this->prestamosQuery($ids)
            ->whereDate('p.fecha_prestamo', '>=', $desde->toDateString())
            ->distinct()
            ->count('p.lector_id');

        $devueltos = $this->prestamosQuery($ids)
            ->whereDate('p.fecha_devolucion', '>=', $desde->toDateString())
            ->selectRaw('SUM(p.estado_prestamo = 1) a_tiempo, SUM(p.estado_prestamo = 2) tardanza')
            ->first();
        $evaluados = (int) $devueltos->a_tiempo + (int) $devueltos->tardanza;

        return [
            'dias' => $dias,
            'total' => $total,
            'sala' => collect($dias)->sum('sala'),
            'casa' => collect($dias)->sum('casa'),
            'lectores' => $lectores,
            'promedio' => $diasConAtencion ? round($total / $diasConAtencion, 1) : 0,
            'dias_atencion' => $diasConAtencion,
            'puntualidad' => $evaluados ? (int) round((int) $devueltos->a_tiempo * 100 / $evaluados) : null,
            'evaluados' => $evaluados,
        ];
    }

    protected function atencion(?array $ids, Collection $activos): array
    {
        $vencidos = $activos
            ->filter(fn (Prestamo $p) => $p->fecha_limite_real && now()->gte($p->fecha_limite_real))
            ->sortBy(fn (Prestamo $p) => $p->fecha_limite_real)
            ->values();

        $reservas = $this->reservasPendientes($ids)
            ->with(['ejemplar:id,libro_id,biblioteca_id', 'ejemplar.libro:id,titulo', 'lector:id,name'])
            ->orderBy('fecha_limite')
            ->orderBy('id')
            ->limit(4)
            ->get();

        return [
            'vencidos' => $vencidos->take(5),
            'vencidos_total' => $vencidos->count(),
            'reservas' => $reservas,
            'reservas_total' => $this->reservasPendientes($ids)->count(),
        ];
    }

    protected function librosMasSolicitados(?array $ids): Collection
    {
        $filas = $this->prestamosQuery($ids)
            ->join('libros as l', 'l.id', '=', 'e.libro_id')
            ->whereDate('p.fecha_prestamo', '>=', today()->subDays(self::DIAS_SERIE - 1)->toDateString())
            ->selectRaw('l.id, l.titulo, COUNT(*) total, COUNT(DISTINCT p.lector_id) lectores')
            ->groupBy('l.id', 'l.titulo')
            ->orderByDesc('total')
            ->orderBy('l.titulo')
            ->limit(5)
            ->get();

        $maximo = max((int) $filas->max('total'), 1);

        return $filas->map(fn ($f) => (object) [
            'id' => $f->id,
            'titulo' => $f->titulo,
            'total' => (int) $f->total,
            'lectores' => (int) $f->lectores,
            'pct' => (int) round($f->total * 100 / $maximo),
        ]);
    }

    protected function carreras(?array $ids): array
    {
        $filas = $this->prestamosQuery($ids)
            ->join('users as u', 'u.id', '=', 'p.lector_id')
            ->leftJoin('personas as pe', 'pe.id', '=', 'u.persona_id')
            ->leftJoin('carreras as c', 'c.id', '=', 'pe.carrera_id')
            ->whereDate('p.fecha_prestamo', '>=', today()->subDays(self::DIAS_SERIE - 1)->toDateString())
            ->selectRaw('c.nombre, COUNT(DISTINCT p.lector_id) lectores, COUNT(*) prestamos')
            ->groupBy('c.nombre')
            ->orderByDesc('lectores')
            ->get();

        $conCarrera = $filas->whereNotNull('nombre')->values();
        $maximo = max((int) $conCarrera->max('lectores'), 1);

        return [
            'top' => $conCarrera->take(5)->map(fn ($f) => (object) [
                'nombre' => self::nombrePropio($f->nombre),
                'lectores' => (int) $f->lectores,
                'prestamos' => (int) $f->prestamos,
                'pct' => (int) round($f->lectores * 100 / $maximo),
            ]),
            'sin_carrera' => (int) $filas->whereNull('nombre')->sum('lectores'),
            'total_carreras' => $conCarrera->count(),
        ];
    }

    protected function demandaPorDia(?array $ids): array
    {
        $conteo = $this->prestamosQuery($ids)
            ->whereDate('p.fecha_prestamo', '>=', today()->subDays(self::DIAS_SEMANA - 1)->toDateString())
            ->selectRaw('DAYOFWEEK(p.fecha_prestamo) dia, COUNT(*) total')
            ->groupBy('dia')
            ->pluck('total', 'dia');

        $dias = collect(self::DIAS_CORTOS)->map(fn ($nombre, $numero) => [
            'dia' => $nombre,
            'nombre' => self::DIAS_LARGOS[$numero],
            'total' => (int) ($conteo[$numero] ?? 0),
        ])->values();

        $maximo = $dias->max('total');
        $pico = $maximo > 0 ? $dias->firstWhere('total', $maximo) : null;

        return [
            'dias' => $dias->all(),
            'pico' => $pico['dia'] ?? null,
            'pico_largo' => $pico['nombre'] ?? null,
            'total' => $dias->sum('total'),
        ];
    }

    protected function coleccion(?array $ids): array
    {
        $ejemplares = DB::table('ejemplares')->when($ids !== null, fn ($q) => $q->whereIn('biblioteca_id', $ids));

        $porEstado = (clone $ejemplares)->selectRaw('estado, COUNT(*) total')->groupBy('estado')->pluck('total', 'estado');
        $total = (int) $porEstado->sum();
        $disponibles = (int) ($porEstado[1] ?? 0);

        $recientes = Libro::query()
            ->when($ids !== null, fn ($q) => $q->whereHas('ejemplares', fn ($e) => $e->whereIn('biblioteca_id', $ids)))
            ->latest()
            ->latest('id')
            ->limit(3)
            ->get(['id', 'titulo', 'created_at']);

        return [
            'ejemplares' => $total,
            'titulos' => (int) (clone $ejemplares)->distinct()->count('libro_id'),
            'disponibles' => $disponibles,
            'prestados' => (int) ($porEstado[0] ?? 0),
            'reservados' => (int) ($porEstado[2] ?? 0),
            'traslado' => (int) ($porEstado[3] ?? 0),
            'pct_disponible' => $total ? round($disponibles * 100 / $total, 1) : 0,
            'nuevos_mes' => (int) (clone $ejemplares)->where('created_at', '>=', today()->startOfMonth())->count(),
            'recientes' => $recientes,
        ];
    }

    protected function movimientosRecientes(?array $ids, int $limite = 7): Collection
    {
        $relaciones = ['ejemplar:id,libro_id,biblioteca_id', 'ejemplar.libro:id,titulo', 'lector:id,name', 'bibliotecario:id,name'];

        $prestamos = $this->enBibliotecas(Prestamo::query(), $ids)->with($relaciones)
            ->whereNotNull('fecha_prestamo')
            ->orderByDesc('fecha_prestamo')->orderByDesc('id')
            ->limit($limite)->get()
            ->map(fn (Prestamo $p) => (object) [
                'tipo' => 'prestamo',
                'momento' => $p->fecha_prestamo,
                'prestamo' => $p,
            ]);

        $devoluciones = $this->enBibliotecas(Prestamo::query(), $ids)->with($relaciones)
            ->whereNotNull('fecha_devolucion')
            ->orderByDesc('fecha_devolucion')->orderByDesc('id')
            ->limit($limite)->get()
            ->map(fn (Prestamo $p) => (object) [
                'tipo' => 'devolucion',
                'momento' => $p->fecha_devolucion,
                'prestamo' => $p,
            ]);

        return $prestamos->concat($devoluciones)
            ->sortByDesc(fn ($m) => $m->momento->timestamp * 10 + ($m->tipo === 'devolucion' ? 1 : 0))
            ->take($limite)
            ->values();
    }

    // Solo cuando el alcance abarca varias bibliotecas.
    protected function comparativoBibliotecas(array $alcance, Collection $activos): Collection
    {
        $ids = $alcance['ids'];
        if ($ids !== null && count($ids) < 2) {
            return collect();
        }

        $bibliotecas = Biblioteca::query()
            ->when($ids !== null, fn ($q) => $q->whereIn('id', $ids))
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        $ejemplares = DB::table('ejemplares')
            ->selectRaw('biblioteca_id, COUNT(*) total, SUM(estado = 1) disponibles')
            ->groupBy('biblioteca_id')
            ->get()
            ->keyBy('biblioteca_id');

        $prestamos30 = $this->prestamosQuery($ids)
            ->whereDate('p.fecha_prestamo', '>=', today()->subDays(self::DIAS_SERIE - 1)->toDateString())
            ->selectRaw('e.biblioteca_id, COUNT(*) total')
            ->groupBy('e.biblioteca_id')
            ->pluck('total', 'biblioteca_id');

        $activosPorBiblioteca = $activos->groupBy(fn (Prestamo $p) => optional($p->ejemplar)->biblioteca_id);

        $filas = $bibliotecas->map(function (Biblioteca $b) use ($ejemplares, $prestamos30, $activosPorBiblioteca) {
            $suyos = $activosPorBiblioteca->get($b->id, collect());
            $total = (int) optional($ejemplares->get($b->id))->total;

            return (object) [
                'id' => $b->id,
                'nombre' => self::nombrePropio($b->nombre),
                'ejemplares' => $total,
                'disponibles' => (int) optional($ejemplares->get($b->id))->disponibles,
                'activos' => $suyos->count(),
                'vencidos' => $suyos->filter(fn (Prestamo $p) => $p->fecha_limite_real && now()->gte($p->fecha_limite_real))->count(),
                'prestamos30' => (int) ($prestamos30[$b->id] ?? 0),
            ];
        })->sortByDesc(fn ($f) => $f->prestamos30 * 1000000 + $f->ejemplares)->values();

        $maximo = max((int) $filas->max('prestamos30'), 1);

        return $filas->map(function ($f) use ($maximo) {
            $f->pct = (int) round($f->prestamos30 * 100 / $maximo);
            return $f;
        });
    }

    protected function visitas(): array
    {
        $desde = today()->subDays(self::DIAS_SERIE - 1);
        $porDia = Visita::selectRaw('fecha, COUNT(*) total')
            ->where('fecha', '>=', $desde->toDateString())
            ->groupBy('fecha')
            ->pluck('total', 'fecha');

        $serie = [];
        for ($i = 0; $i < self::DIAS_SERIE; $i++) {
            $fecha = $desde->copy()->addDays($i);
            $serie[] = [
                'fecha' => $fecha->toDateString(),
                'etiqueta' => $fecha->locale('es')->isoFormat('D MMM'),
                'etiqueta_larga' => Str::ucfirst($fecha->locale('es')->isoFormat('ddd D [de] MMMM')),
                'total' => (int) ($porDia[$fecha->toDateString()] ?? 0),
            ];
        }

        return [
            'hoy' => Visita::whereDate('fecha', today())->count(),
            'semana' => Visita::whereBetween('fecha', [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()])->count(),
            'mes' => Visita::whereYear('fecha', now()->year)->whereMonth('fecha', now()->month)->count(),
            'total' => Visita::count(),
            'serie' => $serie,
            'promedio' => (int) round(collect($serie)->avg('total')),
        ];
    }

    // ---------------------------------------------------------------- accesos

    protected function catalogoAccesos(): array
    {
        return [
            'Circulación' => [
                ['permiso' => 'prestamos.registro', 'titulo' => 'Préstamos y devoluciones', 'copia' => 'Registra préstamos directos, libres y devoluciones.', 'icono' => 'bi-arrow-left-right', 'url' => url('/prestamos/registro')],
                ['permiso' => 'prestamos.reservas', 'titulo' => 'Reservas', 'copia' => 'Entrega o cancela las reservas por recoger.', 'icono' => 'bi-bookmark-check', 'url' => url('/prestamos/reservas')],
                ['permiso' => 'prestamos.multas', 'titulo' => 'Multas y sanciones', 'copia' => 'Registra y levanta sanciones de tu biblioteca.', 'icono' => 'bi-shield-exclamation', 'url' => url('/prestamos/multas')],
            ],
            'Lectores' => [
                ['permiso' => 'lectores.registro', 'titulo' => 'Registro de lectores', 'copia' => 'Altas, carnet original o duplicado y datos.', 'icono' => 'bi-person-vcard', 'url' => url('/lectores/registro')],
                ['permiso' => 'lectores.historial', 'titulo' => 'Historial', 'copia' => 'Movimientos de cada lector.', 'icono' => 'bi-clock-history', 'url' => url('/lectores/historial')],
                ['permiso' => 'lectores.penalizaciones', 'titulo' => 'Penalizaciones', 'copia' => 'Lectores con sanciones vigentes.', 'icono' => 'bi-person-x', 'url' => url('/lectores/penalizaciones')],
                ['permiso' => 'lectores.importacion', 'titulo' => 'Importar lectores', 'copia' => 'Carga masiva desde Excel.', 'icono' => 'bi-people', 'url' => url('/lectores/importacion')],
            ],
            'Catálogo e inventario' => [
                ['permiso' => 'administracion.libros', 'titulo' => 'Libros', 'copia' => 'Consulta y edita el catálogo.', 'icono' => 'bi-book', 'url' => url('/administracion/libros')],
                ['permiso' => 'administracion.libros', 'titulo' => 'Nuevo libro', 'copia' => 'Registra un título y sus ejemplares.', 'icono' => 'bi-plus-square', 'url' => url('/administracion/libros_nuevo')],
                ['permiso' => 'administracion.libros.importar', 'titulo' => 'Importar libros', 'copia' => 'Carga masiva desde Excel.', 'icono' => 'bi-file-earmark-spreadsheet', 'url' => route('administracion.libros.importar')],
                ['permiso' => 'administracion.libros', 'titulo' => 'Traslados', 'copia' => 'Envíos de ejemplares entre sedes.', 'icono' => 'bi-truck', 'url' => url('/administracion/traslados_ejemplares')],
                ['permiso' => 'inventario.fisico', 'titulo' => 'Inventario físico', 'copia' => 'Reporte de existencias por sede.', 'icono' => 'bi-clipboard-check', 'url' => url('/inventario/fisico')],
                ['permiso' => 'administracion.libros', 'titulo' => 'Aprendizaje Dewey y Cutter', 'copia' => 'Cómo se sugiere la clasificación.', 'icono' => 'bi-lightbulb', 'url' => route('manual.aprendizaje.clasificacion')],
            ],
            'Gestión' => [
                ['permiso' => 'reportes.grafico', 'titulo' => 'Reportes', 'copia' => 'Gráficos y descargas.', 'icono' => 'bi-bar-chart-line', 'url' => url('/reportes/grafico')],
                ['permiso' => 'administracion.actividades', 'titulo' => 'Actividades', 'copia' => 'Agenda pública de eventos.', 'icono' => 'bi-calendar-event', 'url' => url('/administracion/actividades')],
                ['permiso' => 'administracion.notificaciones', 'titulo' => 'Notificaciones', 'copia' => 'Avisos para lectores y personal.', 'icono' => 'bi-megaphone', 'url' => url('/administracion/notificaciones')],
                ['permiso' => 'administracion.usuarios', 'titulo' => 'Usuarios y roles', 'copia' => 'Cuentas del personal y permisos.', 'icono' => 'bi-person-gear', 'url' => url('/administracion/usuarios')],
            ],
        ];
    }

    protected function accesos(Collection $codigos): array
    {
        return collect($this->catalogoAccesos())
            ->map(fn ($items) => array_values(array_filter($items, fn ($item) => $codigos->contains($item['permiso']))))
            ->filter()
            ->all();
    }

    protected function accionesPrincipales(Collection $codigos): array
    {
        $acciones = [
            ['permiso' => 'prestamos.registro', 'titulo' => 'Nuevo préstamo', 'icono' => 'bi-plus-lg', 'url' => url('/prestamos/registro')],
            ['permiso' => 'prestamos.reservas', 'titulo' => 'Reservas', 'icono' => 'bi-bookmark-check', 'url' => url('/prestamos/reservas')],
            ['permiso' => 'lectores.registro', 'titulo' => 'Lectores', 'icono' => 'bi-person-vcard', 'url' => url('/lectores/registro')],
            ['permiso' => 'administracion.libros', 'titulo' => 'Nuevo libro', 'icono' => 'bi-journal-plus', 'url' => url('/administracion/libros_nuevo')],
            ['permiso' => 'prestamos.multas', 'titulo' => 'Sanciones', 'icono' => 'bi-shield-exclamation', 'url' => url('/prestamos/multas')],
        ];

        return array_slice(array_values(array_filter($acciones, fn ($a) => $codigos->contains($a['permiso']))), 0, 3);
    }
}
