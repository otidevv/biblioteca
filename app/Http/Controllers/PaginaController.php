<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dewey;
use App\Models\Libro;
use App\Models\Autor;
use App\Models\Materia;
use App\Models\Idioma;
use App\Models\Editorial;
use App\Models\Biblioteca;
use App\Models\Ejemplar;
use App\Models\Reservacion;
use App\Models\ConstanciaLector;
use App\Models\Prestamo;
use App\Models\Sancion;
use App\Services\EstadisticasLectorService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use App\Models\Actividad;
use App\Models\ActividadCategoria;
use App\Models\Tipo_registro;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
class PaginaController extends Controller
{
    //    
    public function index(Request $request)
    {

        $bibliotecas = Biblioteca::all();
        // El campo `imagen` en BD no contiene la palabra "placeholder"; el
        // placeholder aparece cuando el navegador dispara `onerror` porque el
        // archivo referenciado no existe en disco. Por eso filtramos aqui
        // verificando la existencia real del archivo antes de tomar los 8.
        $libros = Libro::with(['autores', 'editorial'])
            ->withAvg('comentarios as rating_promedio', 'calificacion')
            ->withCount('comentarios')
            ->whereHas('ejemplares', function ($query) {
                $query->whereNotNull('biblioteca_id');
            })
            ->whereNotNull('imagen')
            ->where('imagen', '<>', '')
            ->where('imagen', 'not like', '%placeholder%')
            ->latest()
            ->take(60)
            ->get()
            ->filter(function ($libro) {
                $imagen = trim((string) $libro->imagen);
                if ($imagen === '') {
                    return false;
                }
                if (\Illuminate\Support\Str::startsWith($imagen, ['http://', 'https://'])) {
                    return true;
                }
                $relativo = ltrim(preg_replace('#^/?storage/#', '', $imagen), '/');
                if (!str_contains($relativo, '/')) {
                    $relativo = 'libros/' . $relativo;
                }
                return file_exists(storage_path('app/public/' . $relativo));
            })
            ->take(8)
            ->values();
        $actividades = Actividad::with('categoria')
            ->where('estado', 1)
            ->where(function ($query) {
                $query->whereNull('fecha_fin')
                    ->orWhereDate('fecha_fin', '>=', now()->toDateString());
            })
            ->orderBy('fecha_inicio')
            ->take(4)
            ->get();

        return view('pagina.index', compact('bibliotecas','libros', 'actividades'));
        /*
            $query = Libro::with(['autores','editorial','materias','idioma','tipo_registro'])
                ->select('id','titulo','imagen');

            // 🔍 Búsqueda general
            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function($q) use ($search) {
                    $q->where('titulo','like',"%{$search}%")
                    ->orWhere('palabras_clave','like',"%{$search}%")
                    ->orWhereHas('autores', function($qa) use ($search) {
                        $qa->where('nombres','like',"%{$search}%")
                            ->orWhere('apellidos','like',"%{$search}%");
                    });
                });
            }

            // 📑 Tipo de registro
            if ($request->filled('registro_id')) {
                $query->where('tipo_registro_id', $request->registro_id);
            }

            // 🌐 Idioma
            if ($request->filled('idioma_id')) {
                $query->where('idioma_id', $request->idioma_id);
            }

            // 👤 Autor
            if ($request->filled('autor_id')) {
                $query->whereHas('autores', function($q) use ($request) {
                    $q->where('id', $request->autor_id);
                });
            }

            // 📚 Materia
            if ($request->filled('materia_id')) {
                $query->where('materia_id', $request->materia_id);
            }

            $libros = $query->paginate(16);

            // Si la petición es AJAX, devolvemos solo el partial
            if ($request->ajax()) {
                return view('pagina._libros', compact('libros'))->render();
            }

            return view('pagina.index', compact('libros'));
        */
    }
    public function catalogo(Request $request)
    {
        $query = Libro::query()
            ->select('libros.*')
            ->with(['autores', 'editorial', 'materias', 'idioma'])
            ->withAvg('comentarios as rating_promedio', 'calificacion')
            ->withCount('comentarios')
            ->whereHas('ejemplares', function ($q) {
                $q->whereNotNull('biblioteca_id');
            });

        if ($request->filled('titulo')) {
            $termino = trim((string) $request->titulo);

            $query->where(function ($q) use ($termino) {
                $q->where('titulo', 'like', '%' . $termino . '%')
                    ->orWhere('palabras_clave', 'like', '%' . $termino . '%')
                    ->orWhere('isbn', 'like', '%' . $termino . '%');
            });
        }

        if ($request->filled('autor_id')) {
            $query->whereHas('autores', function($q) use ($request){
                $q->where('autores.id', $request->autor_id);
            });
        }

        if ($request->filled('editorial_id')) {
            $query->where('editorial_id',$request->editorial_id);
        }

        if ($request->filled('idioma_id')) {
            $query->where('idioma', $request->idioma_id);
        }

        if ($request->filled('materia_id')) {
            $query->whereHas('materias', function ($q) use ($request) {
                $q->where('materias.id', $request->materia_id);
            });
        }

        $terminoCodigo = $request->filled('codigo_ant') ? trim((string) $request->codigo_ant) : null;

        if ($terminoCodigo) {
            $query->where(function ($q) use ($terminoCodigo) {
                $q->where('codigo_ant', 'like', '%' . $terminoCodigo . '%')
                    ->orWhereHas('ejemplares', function ($eq) use ($terminoCodigo) {
                        $eq->where('codigo_ant', 'like', '%' . $terminoCodigo . '%');
                    });
            });
        }

        $perPage = in_array((int) $request->per_page, [8, 16, 24, 32]) ? (int) $request->per_page : 8;

        // Un libro puede tener ejemplares en varias bibliotecas: se agrupa por
        // (libro, biblioteca) para mostrar un resultado por cada biblioteca donde está disponible.
        $libroIds = $query->select('libros.id')->distinct()->pluck('libros.id');

        $librosQuery = Ejemplar::query()
            ->select('libro_id', 'biblioteca_id')
            ->selectRaw('COUNT(*) as total_ejemplares')
            ->selectRaw('SUM(CASE WHEN estado = ? THEN 1 ELSE 0 END) as disponibles', [Ejemplar::ESTADO_DISPONIBLE])
            ->selectRaw('MAX(codigo_ant) as codigo_ant_biblioteca')
            ->whereIn('libro_id', $libroIds)
            ->whereNotNull('biblioteca_id');

        // Si se busco por codigo, solo se muestra la biblioteca cuyos ejemplares
        // realmente tienen ese codigo (no todas las bibliotecas del libro).
        if ($terminoCodigo) {
            $librosQuery->whereExists(function ($sub) use ($terminoCodigo) {
                $sub->select(DB::raw(1))
                    ->from('ejemplares as e2')
                    ->whereColumn('e2.libro_id', 'ejemplares.libro_id')
                    ->whereColumn('e2.biblioteca_id', 'ejemplares.biblioteca_id')
                    ->where('e2.codigo_ant', 'like', '%' . $terminoCodigo . '%');
            });
        }

        $libros = $librosQuery
            ->groupBy('libro_id', 'biblioteca_id')
            ->orderBy('libro_id')
            ->orderBy('biblioteca_id')
            ->paginate($perPage)
            ->withQueryString();

        $librosPorId = Libro::with(['autores', 'editorial', 'materias', 'idioma'])
            ->withAvg('comentarios as rating_promedio', 'calificacion')
            ->withCount('comentarios')
            ->whereIn('id', $libros->getCollection()->pluck('libro_id')->unique())
            ->get()
            ->keyBy('id');

        $bibliotecasPorId = Biblioteca::whereIn('id', $libros->getCollection()->pluck('biblioteca_id')->unique())
            ->get()
            ->keyBy('id');

        $libros->setCollection(
            $libros->getCollection()
                ->map(function ($grupo) use ($librosPorId, $bibliotecasPorId) {
                    $libro = $librosPorId->get($grupo->libro_id);

                    if (!$libro) {
                        return null;
                    }

                    $libro = clone $libro;
                    $libro->biblioteca_actual = $bibliotecasPorId->get($grupo->biblioteca_id);
                    $libro->total_ejemplares_biblioteca = (int) $grupo->total_ejemplares;
                    $libro->disponibles_biblioteca = (int) $grupo->disponibles;
                    if ($grupo->codigo_ant_biblioteca) {
                        $libro->codigo_ant = $grupo->codigo_ant_biblioteca;
                    }

                    return $libro;
                })
                ->filter()
                ->values()
        );

        // 🔥 SI ES AJAX → SOLO DEVUELVE LA LISTA
        if ($request->ajax()) {
            return view('pagina._libros', compact('libros'))->render();
        }


        $autorSeleccionado = $request->filled('autor_id')
            ? Autor::find($request->autor_id)
            : null;
        $idiomaSeleccionado = $request->filled('idioma_id')
            ? Idioma::find($request->idioma_id)
            : null;
        $materiaSeleccionada = $request->filled('materia_id')
            ? Materia::find($request->materia_id)
            : null;

        return view('pagina.catalogo', compact('libros', 'autorSeleccionado', 'idiomaSeleccionado', 'materiaSeleccionada'));
    }


    public function showBiblioteca(Request $request,$id)
    {
        $biblioteca = Biblioteca::findOrFail($id);
        
        $query = Libro::with(['autores','editorial','ejemplares'])
            ->withAvg('comentarios as rating_promedio', 'calificacion')
            ->withCount('comentarios')
            ->whereHas('ejemplares', function($q) use ($id) {
                $q->where('biblioteca_id', $id);
            });

        if ($request->titulo) {
            $query->where('titulo','like','%'.$request->titulo.'%');
        }

        if ($request->autor_id) {
            $query->whereHas('autores', function($q) use ($request){
                $q->where('autores.id', $request->autor_id);
            });
        }

        if ($request->editorial_id) {
            $query->where('editorial_id',$request->editorial_id);
        }

        if ($request->materia) {
            $query->where('materia',$request->materia);
        }

        if ($request->filled('codigo_ant')) {
            $termino = trim((string) $request->codigo_ant);

            $query->where(function ($q) use ($termino, $id) {
                $q->where('codigo_ant', 'like', '%' . $termino . '%')
                    ->orWhereHas('ejemplares', function ($eq) use ($termino, $id) {
                        $eq->where('biblioteca_id', $id)
                            ->where('codigo_ant', 'like', '%' . $termino . '%');
                    });
            });
        }

        $libros = $query->paginate(8)->withQueryString();

        $libros->getCollection()->each(function ($libro) use ($biblioteca) {
            $ejemplaresBiblioteca = $libro->ejemplares->where('biblioteca_id', $biblioteca->id);
            $libro->biblioteca_actual = $biblioteca;
            $libro->total_ejemplares_biblioteca = $ejemplaresBiblioteca->count();
            $libro->disponibles_biblioteca = $ejemplaresBiblioteca->where('estado', Ejemplar::ESTADO_DISPONIBLE)->count();

            $codigoAntBiblioteca = $ejemplaresBiblioteca->pluck('codigo_ant')->filter()->first();
            if ($codigoAntBiblioteca) {
                $libro->codigo_ant = $codigoAntBiblioteca;
            }
        });

        // 🔥 SI ES AJAX → SOLO DEVUELVE LA LISTA
        if ($request->ajax()) {
            return view('pagina._libros', compact('libros'))->render();
        }


        return view('pagina.catalogo', compact('libros','biblioteca'));
    }

    public function showLibro(Request $request, $id)
    {
        // Traer el libro con todas sus relaciones, incluyendo ejemplares y biblioteca
        $libro = Libro::with([
            'autores',
            'editorial',
            'materias',
            'idioma',
            'tipo_registro',
            'comentarios.usuario',
            'ejemplares.biblioteca' // 👈 aquí traes los ejemplares y su biblioteca
        ])
        ->withAvg('comentarios as rating_promedio', 'calificacion')
        ->withCount('comentarios')
        ->findOrFail($id);

        // Si se llega desde una tarjeta del catálogo scopeada a una biblioteca,
        // la ficha solo muestra la disponibilidad de esa sede.
        $bibliotecaFiltro = null;
        if ($request->filled('biblioteca')) {
            $bibliotecaFiltroId = (int) $request->query('biblioteca');
            if ($libro->ejemplares->contains('biblioteca_id', $bibliotecaFiltroId)) {
                $bibliotecaFiltro = Biblioteca::find($bibliotecaFiltroId);
            }
        }

        //OBTENER BIBLIOTECAS QUE TIENEN ESE LIBRO
        $bibliotecas = Biblioteca::whereHas('ejemplares', function($q) use ($id, $bibliotecaFiltro) {
            $q->where('libro_id', $id)
            ->where('estado', 1); // solo disponibles

            if ($bibliotecaFiltro) {
                $q->where('biblioteca_id', $bibliotecaFiltro->id);
            }
        })->get();
        // Palabras clave
        $keywords = collect(explode(' ', $libro->titulo))
            ->merge(explode(' ', $libro->palabras_clave ?? ''))
            ->filter(fn($word) => strlen($word) > 3)
            ->unique();

        // Libros relacionados
        $libros = Libro::with(['autores','editorial','materias','idioma','tipo_registro'])
            ->withAvg('comentarios as rating_promedio', 'calificacion')
            ->withCount('comentarios')
            ->where('id', '!=', $libro->id)
            ->where(function($q) use ($libro, $keywords) {
                $q->whereHas('materias', function($mq) use ($libro) {
                    $mq->whereIn('materias.id', $libro->materias->pluck('id'));
                });

                if ($keywords->isNotEmpty()) {
                    $q->orWhere(function($sub) use ($keywords) {
                        foreach ($keywords as $word) {
                            $sub->orWhere('titulo','like',"%{$word}%")
                                ->orWhere('palabras_clave','like',"%{$word}%")
                                ->orWhere('palabras_clave','like',"%{$word}%");
                        }
                    });
                }
            })
            ->limit(4)
            ->get();

        $miComentario = auth()->check()
            ? $libro->comentarios->firstWhere('user_id', auth()->id())
            : null;

        return view('pagina.libro', compact('libro','libros','bibliotecas','miComentario','bibliotecaFiltro'));
    }
    // Panel del lector: pagina principal del estudiante al iniciar sesion.
    public function panelLector(EstadisticasLectorService $servicio)
    {
        $user = auth()->user()->load('persona.carrera');
        $estadisticas = $servicio->calcular($user);

        $prestamosActivos = Prestamo::with(['ejemplar.libro', 'ejemplar.biblioteca'])
            ->where('lector_id', $user->id)
            ->where('estado', 1)
            ->get()
            ->sortBy(fn ($p) => $p->fecha_limite_real)
            ->values();

        $fueraDePlazo = $prestamosActivos->filter(fn ($p) => $p->fecha_limite_real && now()->gte($p->fecha_limite_real));

        $reservasPendientes = Reservacion::with(['ejemplar.libro', 'ejemplar.biblioteca'])
            ->where('lector_id', $user->id)
            ->where('estado', 0)
            ->get()
            ->filter(fn ($r) => now()->lt($r->fecha_limite_real))
            ->sortBy(fn ($r) => $r->fecha_limite_real)
            ->values();

        $sanciones = Sancion::with('biblioteca')
            ->where('user_id', $user->id)
            ->where('estado', 1)
            ->whereDate('fecha_fin', '>=', today())
            ->orderBy('fecha_fin')
            ->get();

        $recientes = Prestamo::with(['ejemplar.libro', 'ejemplar.biblioteca'])
            ->where('lector_id', $user->id)
            ->where('estado', 2)
            ->latest('fecha_devolucion')
            ->limit(5)
            ->get();

        $totalPrestamos = Prestamo::where('lector_id', $user->id)->count();

        return view('pagina.panel_lector', compact(
            'user',
            'prestamosActivos',
            'fueraDePlazo',
            'reservasPendientes',
            'sanciones',
            'recientes',
            'totalPrestamos',
            'estadisticas'
        ));
    }

    // Constancia de lector frecuente: se emite desde el panel si el nivel lo permite.
    public function emitirConstancia(EstadisticasLectorService $servicio)
    {
        $constancia = $servicio->emitirConstancia(auth()->user());

        if (! $constancia) {
            return redirect()
                ->route('lector.panel')
                ->with('panel_error', 'Aún no alcanzas el nivel de lector frecuente para emitir la constancia.');
        }

        return redirect()->route('lector.constancia', $constancia->codigo);
    }

    public function verConstancia(string $codigo)
    {
        $constancia = ConstanciaLector::where('codigo', $codigo)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return Pdf::loadView('pagina.constancia_lector_pdf', [
            'constancia' => $constancia,
            'urlVerificacion' => route('constancias.verificar', $constancia->codigo),
            'logo' => $this->logoBase64(),
        ])
            ->setPaper('a4')
            ->stream('constancia-lector-' . $constancia->codigo . '.pdf');
    }

    // Reporte de lectura del panel en PDF (cualquier nivel; no reemplaza a la constancia).
    public function reporteLector(EstadisticasLectorService $servicio)
    {
        $user = auth()->user()->load('persona.carrera');

        $historial = Prestamo::with(['ejemplar.libro', 'ejemplar.biblioteca'])
            ->where('lector_id', $user->id)
            ->latest('fecha_prestamo')
            ->limit(10)
            ->get();

        $identificador = $user->persona?->codigo_institucional ?: $user->id;

        return Pdf::loadView('pagina.reporte_lector_pdf', [
            'user' => $user,
            'estadisticas' => $servicio->calcular($user),
            'historial' => $historial,
            'logo' => $this->logoBase64(),
        ])
            ->setPaper('a4')
            ->stream('reporte-lectura-' . $identificador . '-' . now()->format('Y-m-d') . '.pdf');
    }

    // Los PDF no cargan recursos remotos: el logo va embebido.
    private function logoBase64(): string
    {
        return 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('img/logo_unamad.png')));
    }

    // Verificacion publica: cualquiera con el codigo puede comprobar la constancia.
    public function verificarConstancia(Request $request, ?string $codigo = null)
    {
        if (! $codigo && $request->filled('codigo')) {
            return redirect()->route('constancias.verificar', Str::upper(trim((string) $request->codigo)));
        }

        $codigo = $codigo !== null ? Str::upper(trim($codigo)) : null;
        $constancia = $codigo && preg_match('/^CL-[A-Z0-9]{4}-[A-Z0-9]{4}$/', $codigo)
            ? ConstanciaLector::where('codigo', $codigo)->first()
            : null;

        return view('pagina.verificar_constancia', compact('codigo', 'constancia'));
    }

    public function misReservas()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user()->load('persona');

        $reservas = Reservacion::with(['ejemplar.libro.autores', 'ejemplar.biblioteca'])
            ->where('lector_id', $user->id)
            ->latest('fecha_reservacion')
            ->latest('id')
            ->get();

        // Una reserva entregada no guarda su prestamo: es el del mismo ejemplar desde la fecha de la reserva.
        $prestamos = Prestamo::where('lector_id', $user->id)->orderBy('fecha_prestamo')->get();
        $prestamoDeReserva = $reservas->where('estado', 1)->mapWithKeys(fn ($r) => [
            $r->id => $prestamos->first(fn ($p) => (int) $p->ejemplar_id === (int) $r->ejemplar_id
                && $p->fecha_prestamo
                && $p->fecha_prestamo->gte($r->fecha_reservacion->copy()->startOfDay())),
        ]);

        $sancionPorReserva = Sancion::where('user_id', $user->id)
            ->whereNotNull('reservacion_id')
            ->get()
            ->keyBy('reservacion_id');

        return view('pagina.mis_reservas', compact('user', 'reservas', 'prestamoDeReserva', 'sancionPorReserva'));
    }

    public function misPrestamos(EstadisticasLectorService $servicio)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user()->load('persona');

        $prestamos = Prestamo::with(['ejemplar.libro.autores', 'ejemplar.biblioteca'])
            ->where('lector_id', $user->id)
            ->latest('fecha_prestamo')
            ->latest('id')
            ->get();

        $sancionesPorPrestamo = Sancion::where('user_id', $user->id)
            ->whereNotNull('prestamo_id')
            ->get()
            ->groupBy('prestamo_id');

        $estadisticas = $servicio->calcular($user);

        return view('pagina.mis_prestamos', compact('user', 'prestamos', 'sancionesPorPrestamo', 'estadisticas'));
    }

    public function eventos()
    {
        // En curso y proximas, de la mas cercana a la mas lejana.
        $vigentes = Actividad::with('categoria')
            ->vigentes()
            ->orderBy('fecha_inicio')
            ->orderBy('hora_inicio')
            ->get();

        // Portada: la destacada mas cercana; si no hay, la proxima.
        $destacado = $vigentes->firstWhere('destacado', true) ?? $vigentes->first();

        $pasados = Actividad::with('categoria')
            ->finalizadas()
            ->orderByDesc('fecha_inicio')
            ->limit(6)
            ->get();

        $categorias = ActividadCategoria::where('estado', 1)->orderBy('nombre')->get();

        return view('pagina.eventos', compact('vigentes', 'destacado', 'pasados', 'categorias'));
    }

    // "Agregar a mi calendario": archivo .ics del evento (Google, Outlook, celular).
    public function eventoCalendario(Actividad $actividad)
    {
        abort_unless((int) $actividad->estado === 1, 404);

        $zona = config('app.timezone');
        $fin = $actividad->fecha_fin ?? $actividad->fecha_inicio;
        $utc = fn ($fecha, $hora) => \Illuminate\Support\Carbon::parse($fecha->format('Y-m-d') . ' ' . $hora->format('H:i'), $zona)
            ->utc()->format('Ymd\THis\Z');

        if ($actividad->hora_inicio) {
            $inicioIcs = 'DTSTART:' . $utc($actividad->fecha_inicio, $actividad->hora_inicio);
            $finIcs = 'DTEND:' . $utc($fin, $actividad->hora_fin ?? $actividad->hora_inicio->copy()->addHour());
        } else {
            // Evento de dia completo: DTEND es exclusivo.
            $inicioIcs = 'DTSTART;VALUE=DATE:' . $actividad->fecha_inicio->format('Ymd');
            $finIcs = 'DTEND;VALUE=DATE:' . $fin->copy()->addDay()->format('Ymd');
        }

        $texto = fn (?string $valor) => str_replace(["\\", ';', ',', "\r\n", "\n"], ['\\\\', '\;', '\,', '\n', '\n'], trim((string) $valor));
        $descripcion = trim(($actividad->resumen ? $actividad->resumen . "\n\n" : '') . strip_tags((string) $actividad->contenido));

        $lineas = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Biblioteca UNAMAD//Agenda//ES',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:actividad-' . $actividad->id . '@' . request()->getHost(),
            'DTSTAMP:' . now()->utc()->format('Ymd\THis\Z'),
            $inicioIcs,
            $finIcs,
            'SUMMARY:' . $texto($actividad->titulo),
            'DESCRIPTION:' . $texto($descripcion),
            'LOCATION:' . $texto($actividad->lugar ?: 'Biblioteca UNAMAD'),
            'URL:' . route('evento') . '#evento-' . $actividad->id,
            'END:VEVENT',
            'END:VCALENDAR',
        ];

        // RFC 5545: lineas de maximo 75 octetos, continuadas con un espacio.
        $ics = collect($lineas)->map(function ($linea) {
            $partes = [];
            while (strlen($linea) > 75) {
                $corte = 75;
                while ($corte > 0 && (ord($linea[$corte]) & 0xC0) === 0x80) {
                    $corte--;   // no partir un caracter UTF-8
                }
                $partes[] = substr($linea, 0, $corte);
                $linea = ' ' . substr($linea, $corte);
            }
            $partes[] = $linea;

            return implode("\r\n", $partes);
        })->implode("\r\n") . "\r\n";

        return response($ics, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="evento-' . $actividad->id . '.ics"',
        ]);
    }

    public function otrasBibliotecas()
    {
        $bibliotecasExternas = collect([
            [
                'nombre' => 'Cybertesis UNI',
                'institucion' => 'Universidad Nacional de Ingeniería',
                'descripcion' => 'Repositorio y portal de tesis digitales para consulta académica.',
                'url' => 'http://cybertesis.uni.edu.pe/',
                'icono' => 'bi-mortarboard-fill',
                'etiqueta' => 'Repositorio',
            ],
            [
                'nombre' => 'IRIS PAHO',
                'institucion' => 'Organización Panamericana de la Salud',
                'descripcion' => 'Colección de documentos y publicaciones especializadas en salud pública.',
                'url' => 'http://iris.paho.org/xmlui/handle/123456789/2936',
                'icono' => 'bi-heart-pulse-fill',
                'etiqueta' => 'Salud',
            ],
            [
                'nombre' => 'Producción Científica LUZ',
                'institucion' => 'Universidad del Zulia',
                'descripcion' => 'Publicaciones y producción científica de acceso abierto.',
                'url' => 'http://produccioncientificaluz.org/',
                'icono' => 'bi-journal-richtext',
                'etiqueta' => 'Repositorio',
            ],
            [
                'nombre' => 'Repositorio UNJFSC',
                'institucion' => 'Universidad Nacional José Faustino Sánchez Carrión',
                'descripcion' => 'Repositorio institucional para investigación, tesis y documentos académicos.',
                'url' => 'http://repositorio.unjfsc.edu.pe/',
                'icono' => 'bi-folder2-open',
                'etiqueta' => 'Repositorio',
            ],
            [
                'nombre' => 'CIES Diagnóstico y Propuesta',
                'institucion' => 'Consorcio de Investigación Económica y Social',
                'descripcion' => 'Publicaciones orientadas a análisis, diagnóstico y propuestas de política pública.',
                'url' => 'http://www.cies.org.pe/es/publicaciones/diagnostico-propuesta',
                'icono' => 'bi-graph-up-arrow',
                'etiqueta' => 'Investigación',
            ],
            [
                'nombre' => 'CIES Investigaciones Breves',
                'institucion' => 'CIES',
                'descripcion' => 'Serie de investigaciones y documentos breves para consulta.',
                'url' => 'http://www.cies.org.pe/es/publicaciones/investigaciones-breves',
                'icono' => 'bi-file-earmark-text',
                'etiqueta' => 'Investigación',
            ],
            [
                'nombre' => 'GRADE Publicaciones',
                'institucion' => 'Grupo de Análisis para el Desarrollo',
                'descripcion' => 'Acceso a estudios y publicaciones de investigación social y económica.',
                'url' => 'http://www.grade.org.pe/publicaciones/',
                'icono' => 'bi-bar-chart-line-fill',
                'etiqueta' => 'Investigación',
            ],
            [
                'nombre' => 'AAEAP Publicaciones',
                'institucion' => 'AAEAP',
                'descripcion' => 'Colección de publicaciones académicas disponibles para consulta.',
                'url' => 'https://aaeap.org.ar/publicaciones/',
                'icono' => 'bi-book',
                'etiqueta' => 'Publicaciones',
            ],
            [
                'nombre' => 'Escuela de Gobierno PUCP',
                'institucion' => 'PUCP',
                'descripcion' => 'Publicaciones de la Escuela de Gobierno y Políticas Públicas.',
                'url' => 'https://escuela.pucp.edu.pe/gobierno/publicaciones/',
                'icono' => 'bi-building-gear',
                'etiqueta' => 'Gobierno',
            ],
            [
                'nombre' => 'FLACSO Publicaciones',
                'institucion' => 'FLACSO Argentina',
                'descripcion' => 'Libros, revistas y publicaciones académicas en ciencias sociales.',
                'url' => 'https://flacso.org.ar/publicaciones/',
                'icono' => 'bi-globe-americas',
                'etiqueta' => 'Ciencias sociales',
            ],
            [
                'nombre' => 'Fondo Editorial Continental',
                'institucion' => 'Universidad Continental',
                'descripcion' => 'Publicaciones y libros de acceso abierto del fondo editorial.',
                'url' => 'https://fondoeditorial.continental.edu.pe/publicaciones/acceso-abierto/',
                'icono' => 'bi-journal-bookmark-fill',
                'etiqueta' => 'Editorial',
            ],
            [
                'nombre' => 'ICAP Publicaciones',
                'institucion' => 'Instituto Centroamericano de Administración Pública',
                'descripcion' => 'Repositorio de publicaciones sobre administración y gestión pública.',
                'url' => 'https://icap.ac.cr/publicaciones-3/',
                'icono' => 'bi-briefcase-fill',
                'etiqueta' => 'Gestión pública',
            ],
            [
                'nombre' => 'IEP Publicaciones',
                'institucion' => 'Instituto de Estudios Peruanos',
                'descripcion' => 'Investigaciones y publicaciones académicas del IEP.',
                'url' => 'https://iep.org.pe/',
                'icono' => 'bi-bank',
                'etiqueta' => 'Investigación',
            ],
            [
                'nombre' => 'INAP México Libros',
                'institucion' => 'Instituto Nacional de Administración Pública',
                'descripcion' => 'Libros especializados en administración pública y políticas.',
                'url' => 'https://inap.mx/investigacion/libros-inap/',
                'icono' => 'bi-book-half',
                'etiqueta' => 'Libros',
            ],
            [
                'nombre' => 'INAP México Revista',
                'institucion' => 'INAP México',
                'descripcion' => 'Revista académica y técnica para consulta en línea.',
                'url' => 'https://inap.mx/investigacion/revista/',
                'icono' => 'bi-newspaper',
                'etiqueta' => 'Revista',
            ],
            [
                'nombre' => 'INAP México Praxis',
                'institucion' => 'INAP México',
                'descripcion' => 'Serie Praxis con documentos y materiales de referencia.',
                'url' => 'https://inap.mx/serie-praxis/',
                'icono' => 'bi-collection',
                'etiqueta' => 'Serie',
            ],
            [
                'nombre' => 'ESAN Ediciones Digitales',
                'institucion' => 'Universidad ESAN',
                'descripcion' => 'Publicaciones y contenido digital para consulta académica.',
                'url' => 'https://investigaciones.esan.edu.pe/esanediciones/producto/digital/',
                'icono' => 'bi-laptop',
                'etiqueta' => 'Digital',
            ],
            [
                'nombre' => 'Publicaciones de la Unión Europea',
                'institucion' => 'European Union',
                'descripcion' => 'Portal oficial de publicaciones y documentos de libre acceso.',
                'url' => 'https://op.europa.eu/en/web/general-publications/publications',
                'icono' => 'bi-flag-fill',
                'etiqueta' => 'Internacional',
            ],
            [
                'nombre' => 'Revista Estado y Políticas Públicas',
                'institucion' => 'FLACSO',
                'descripcion' => 'Edición disponible para consulta en línea.',
                'url' => 'https://revistaeypp.flacso.org.ar/revista/numero-22_206',
                'icono' => 'bi-file-earmark-medical',
                'etiqueta' => 'Revista',
            ],
            [
                'nombre' => 'Ulibros',
                'institucion' => 'Plataforma de publicaciones',
                'descripcion' => 'Sitio de acceso y consulta de libros y publicaciones digitales.',
                'url' => 'https://ulibros.com/',
                'icono' => 'bi-bookmarks-fill',
                'etiqueta' => 'Biblioteca digital',
            ],
            [
                'nombre' => 'CEPAL eLAC',
                'institucion' => 'CEPAL',
                'descripcion' => 'Recursos y documentación vinculados a transformación digital y desarrollo.',
                'url' => 'https://www.cepal.org/es/proyectos/elac2022',
                'icono' => 'bi-diagram-3-fill',
                'etiqueta' => 'CEPAL',
            ],
            [
                'nombre' => 'CLADEA',
                'institucion' => 'Consejo Latinoamericano de Escuelas de Administración',
                'descripcion' => 'Portal institucional con recursos y publicaciones académicas.',
                'url' => 'https://www.cladea.org/es/',
                'icono' => 'bi-people-fill',
                'etiqueta' => 'Red académica',
            ],
            [
                'nombre' => 'CLADEA Investigaciones',
                'institucion' => 'CLADEA',
                'descripcion' => 'Sección de investigaciones y contenido especializado.',
                'url' => 'https://www.cladea.org/es/investigaciones',
                'icono' => 'bi-search-heart',
                'etiqueta' => 'Investigación',
            ],
            [
                'nombre' => 'Cooperación Suiza SECO',
                'institucion' => 'Cooperación Suiza en Perú',
                'descripcion' => 'Publicaciones y documentos organizados por categoría.',
                'url' => 'https://www.cooperacionsuiza.pe/categoria_de_publicacion/seco/',
                'icono' => 'bi-globe2',
                'etiqueta' => 'Cooperación',
            ],
            [
                'nombre' => 'Defensoría del Pueblo',
                'institucion' => 'Perú',
                'descripcion' => 'Portal de documentos, informes y publicaciones institucionales.',
                'url' => 'https://www.defensoria.gob.pe/',
                'icono' => 'bi-shield-check',
                'etiqueta' => 'Institucional',
            ],
            [
                'nombre' => 'EAP UCR Documentos',
                'institucion' => 'Universidad de Costa Rica',
                'descripcion' => 'Documentos y recursos de consulta de la unidad académica.',
                'url' => 'https://www.eap.ucr.ac.cr/index.php/documentos',
                'icono' => 'bi-file-earmark-ruled',
                'etiqueta' => 'Documentos',
            ],
            [
                'nombre' => 'ESAP Publicaciones',
                'institucion' => 'Escuela Superior de Administración Pública',
                'descripcion' => 'Publicaciones de la ESAP para consulta abierta.',
                'url' => 'https://www.esap.edu.co/portal/index.php/publicaciones-esap/',
                'icono' => 'bi-building-fill-check',
                'etiqueta' => 'Administración pública',
            ],
            [
                'nombre' => 'FES Perú Publicaciones',
                'institucion' => 'Fundación Friedrich Ebert',
                'descripcion' => 'Colección de publicaciones y análisis disponibles en línea.',
                'url' => 'https://www.fes-peru.org/publicaciones/',
                'icono' => 'bi-lightbulb-fill',
                'etiqueta' => 'Análisis',
            ],
            [
                'nombre' => 'Fundación Carolina',
                'institucion' => 'España',
                'descripcion' => 'Estudios, análisis y publicaciones para consulta.',
                'url' => 'https://www.fundacioncarolina.es/estudios-analisis/publicaciones/',
                'icono' => 'bi-journal-text',
                'etiqueta' => 'Estudios',
            ],
            [
                'nombre' => 'Fundación Telefónica',
                'institucion' => 'Cultura Digital',
                'descripcion' => 'Publicaciones y contenidos sobre innovación, educación y cultura digital.',
                'url' => 'https://www.fundaciontelefonica.com/cultura-digital/publicaciones/',
                'icono' => 'bi-cpu-fill',
                'etiqueta' => 'Cultura digital',
            ],
            [
                'nombre' => 'Comunidad Andina',
                'institucion' => 'CAN',
                'descripcion' => 'Normas y documentos institucionales de consulta.',
                'url' => 'https://www.gob.pe/institucion/can/normas-y-documentos',
                'icono' => 'bi-file-earmark-break',
                'etiqueta' => 'Normativa',
            ],
            [
                'nombre' => 'MTPE Normas y documentos',
                'institucion' => 'Ministerio de Trabajo y Promoción del Empleo',
                'descripcion' => 'Documentos y normativa institucional disponible en línea.',
                'url' => 'https://www.gob.pe/institucion/mtpe/normas-y-documentos',
                'icono' => 'bi-file-earmark-check-fill',
                'etiqueta' => 'Normativa',
            ],
            [
                'nombre' => 'SUNAFIL',
                'institucion' => 'Perú',
                'descripcion' => 'Portal institucional con recursos, normativa y publicaciones.',
                'url' => 'https://www.gob.pe/sunafil',
                'icono' => 'bi-person-workspace',
                'etiqueta' => 'Institucional',
            ],
            [
                'nombre' => 'Biblioteca IAEN',
                'institucion' => 'Instituto de Altos Estudios Nacionales',
                'descripcion' => 'Biblioteca y recursos de consulta académica del IAEN.',
                'url' => 'https://www.iaen.edu.ec/la-universidad/biblioteca/',
                'icono' => 'bi-building',
                'etiqueta' => 'Biblioteca',
            ],
            [
                'nombre' => 'IDEA Internacional',
                'institucion' => 'International IDEA',
                'descripcion' => 'Publicaciones internacionales sobre democracia y gobernanza.',
                'url' => 'https://www.idea.int/es/publications',
                'icono' => 'bi-globe-central-south-asia',
                'etiqueta' => 'Internacional',
            ],
            [
                'nombre' => 'INAP España',
                'institucion' => 'Instituto Nacional de Administración Pública',
                'descripcion' => 'Publicaciones y materiales de formación y gestión pública.',
                'url' => 'https://www.inap.es/publicaciones',
                'icono' => 'bi-postcard-fill',
                'etiqueta' => 'Publicaciones',
            ],
            [
                'nombre' => 'INEI Publicaciones Digitales',
                'institucion' => 'Instituto Nacional de Estadística e Informática',
                'descripcion' => 'Biblioteca virtual con publicaciones digitales y estadísticas.',
                'url' => 'https://www.inei.gob.pe/biblioteca-virtual/publicaciones-digitales/',
                'icono' => 'bi-clipboard-data-fill',
                'etiqueta' => 'Estadística',
            ],
            [
                'nombre' => 'La Referencia',
                'institucion' => 'Red Federada de Repositorios',
                'descripcion' => 'Plataforma regional de acceso abierto a producción científica.',
                'url' => 'https://www.lareferencia.info/es/',
                'icono' => 'bi-share-fill',
                'etiqueta' => 'Acceso abierto',
            ],
            [
                'nombre' => 'Librería Virtual I',
                'institucion' => 'Plataforma digital',
                'descripcion' => 'Portal de acceso a publicaciones y recursos en línea.',
                'url' => 'https://www.libreriavirtuali.com/inicio',
                'icono' => 'bi-cart4',
                'etiqueta' => 'Plataforma',
            ],
            [
                'nombre' => 'Conectamef',
                'institucion' => 'Ministerio de Economía y Finanzas',
                'descripcion' => 'Repositorio de contenidos y servicios del MEF.',
                'url' => 'https://www.mef.gob.pe/contenidos/servicios_web/conectamef/',
                'icono' => 'bi-cash-coin',
                'etiqueta' => 'Economía',
            ],
            [
                'nombre' => 'Red Innovación',
                'institucion' => 'Plataforma regional',
                'descripcion' => 'Recursos, contenidos y publicaciones en innovación pública.',
                'url' => 'https://www.redinnovacion.org/',
                'icono' => 'bi-stars',
                'etiqueta' => 'Innovación',
            ],
            [
                'nombre' => 'SEDH Honduras',
                'institucion' => 'Observatorio de Derechos Humanos',
                'descripcion' => 'Informes y publicaciones sobre derechos humanos.',
                'url' => 'https://www.sedh.gob.hn/odh/publicaciones/informes',
                'icono' => 'bi-shield-fill-check',
                'etiqueta' => 'Informes',
            ],
            [
                'nombre' => 'Servicio Civil Chile',
                'institucion' => 'Chile',
                'descripcion' => 'Documentos y recursos institucionales de consulta pública.',
                'url' => 'https://www.serviciocivil.cl/documentos-3/',
                'icono' => 'bi-person-badge-fill',
                'etiqueta' => 'Documentos',
            ],
            [
                'nombre' => 'UCLG Publicaciones',
                'institucion' => 'United Cities and Local Governments',
                'descripcion' => 'Publicaciones y recursos internacionales de ciudades y gobiernos locales.',
                'url' => 'https://www.uclg.org/es/recursos/publicaciones',
                'icono' => 'bi-buildings-fill',
                'etiqueta' => 'Gobiernos locales',
            ],
        ]);

        return view('pagina.otras_bibliotecas', compact('bibliotecasExternas'));
    }

    public function bibliotecasCientificas()
    {
        $bases = [
            [
                'nombre'      => 'ScienceDirect',
                'descripcion' => 'Plataforma de Elsevier con acceso a revistas científicas, libros y artículos de investigación en ciencias, tecnología, medicina y ciencias sociales.',
                'tipo_acceso' => 'cuenta',
                'acceso'      => 'Crea tu cuenta con tu correo UNAMAD',
                'detalle'     => 'Ingresa a la plataforma y regístrate usando tu correo @unamad.edu.pe. Es gratuito y te da acceso completo.',
                'url'         => 'https://www.sciencedirect.com/',
                'logo'        => 'img/bibliotecas_cientificas/ScienceDirect_logo.png',
                'vigencia'    => null,
            ],
            [
                'nombre'      => 'Scopus',
                'descripcion' => 'Base de datos bibliográfica de Elsevier que indexa literatura científica revisada por pares: revistas, libros y actas de conferencias.',
                'tipo_acceso' => 'cuenta',
                'acceso'      => 'Crea tu cuenta con tu correo UNAMAD',
                'detalle'     => 'Ingresa a la plataforma y regístrate usando tu correo @unamad.edu.pe. Es gratuito y te da acceso completo.',
                'url'         => 'https://www.scopus.com/',
                'logo'        => 'img/bibliotecas_cientificas/Scopus_logo.png',
                'vigencia'    => null,
            ],
            [
                'nombre'      => 'IOPScience',
                'descripcion' => 'Plataforma del Institute of Physics con publicaciones en física, ingeniería, astrofísica y ciencias relacionadas.',
                'tipo_acceso' => 'red',
                'acceso'      => 'Conéctate a la red o WiFi de UNAMAD',
                'detalle'     => 'El acceso es automático si estás dentro del campus o conectado al WiFi institucional. No necesitas contraseña.',
                'url'         => 'https://iopscience.iop.org/',
                'logo'        => 'img/bibliotecas_cientificas/IOPSCIENCE_logo.png',
                'vigencia'    => '19/05/2026 – 19/05/2027',
            ],
        ];

        return view('pagina.bibliotecas_cientificas', compact('bases'));
    }

}
