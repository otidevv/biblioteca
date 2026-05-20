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
use App\Models\Reservacion;
use App\Models\Prestamo;
use App\Models\Actividad;
use App\Models\ActividadCategoria;
use App\Models\Tipo_registro;
use Illuminate\Support\Facades\Schema;
class PaginaController extends Controller
{
    //    
    public function index(Request $request)
    {

        $bibliotecas = Biblioteca::all();
        $libros = Libro::with(['autores', 'editorial'])
            ->withAvg('comentarios as rating_promedio', 'calificacion')
            ->withCount('comentarios')
            ->whereHas('ejemplares', function ($query) {
                $query->whereNotNull('biblioteca_id');
            })
            ->latest()
            ->take(8)
            ->get();
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

        if ($request->filled('codigo_ant')) {
            $query->where('codigo_ant', 'like', '%' . trim((string) $request->codigo_ant) . '%');
        }

        $perPage = in_array((int) $request->per_page, [8, 16, 24, 32]) ? (int) $request->per_page : 8;
        $libros  = $query->distinct('libros.id')->paginate($perPage)->withQueryString();

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

        $libros = $query->paginate(8)->withQueryString();

        // 🔥 SI ES AJAX → SOLO DEVUELVE LA LISTA
        if ($request->ajax()) {
            return view('pagina._libros', compact('libros'))->render();
        }


        return view('pagina.catalogo', compact('libros','biblioteca'));
    }

    public function showLibro($id)
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
        //OBTENER BIBLIOTECAS QUE TIENEN ESE LIBRO
        $bibliotecas = Biblioteca::whereHas('ejemplares', function($q) use ($id) {
            $q->where('libro_id', $id)
            ->where('estado', 1); // solo disponibles
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

        return view('pagina.libro', compact('libro','libros','bibliotecas'));
    }
    public function misReservas()
    {        
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $reservas = Reservacion::with(['ejemplar.libro', 'ejemplar.biblioteca'])
            ->where('lector_id', auth()->id())
            ->latest()
            ->get();

        return view('pagina.mis_reservas', compact('reservas'));
    }

    public function misPrestamos()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $prestamos = Prestamo::with(['ejemplar.libro', 'ejemplar.biblioteca'])
            ->where('lector_id', auth()->id())
            ->latest('fecha_prestamo')
            ->get();

        return view('pagina.mis_prestamos', compact('prestamos'));
    }

    public function eventos()
    {
        $eventosQuery = Actividad::with('categoria')
            ->where('estado', 1);

        if (Schema::hasColumn('actividades', 'destacado')) {
            $eventosQuery->orderByDesc('destacado');
        }

        $eventosDestacados = $eventosQuery
            ->orderBy('fecha_inicio')
            ->limit(6)
            ->get();

        $categorias = ActividadCategoria::withCount(['actividades' => function ($query) {
                $query->where('estado', 1);
            }])
            ->where('estado', 1)
            ->orderBy('nombre')
            ->get();

        $agenda = Actividad::with('categoria')
            ->where('estado', 1)
            ->orderBy('fecha_inicio')
            ->limit(6)
            ->get();

        return view('pagina.eventos', compact('eventosDestacados', 'agenda', 'categorias'));
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
