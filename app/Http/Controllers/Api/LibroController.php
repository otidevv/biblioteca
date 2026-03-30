<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use App\Models\Dewey;
use App\Models\Dewey_aprendizaje;
use App\Models\Libro;
class LibroController extends Controller
{
    private array $stopwords = [
        'de', 'la', 'el', 'y', 'en', 'los', 'las', 'un', 'una', 'por', 'para',
        'con', 'del', 'al', 'a', 'o', 'u', 'que', 'se', 'su', 'sus', 'como',
    ];

    //
    public function listar(Request $request)
    {
        $query = Libro::with(['autores','tipo_registro'])
                    ->withCount('ejemplares');

        return DataTables::of($query)

        //  FILTRO PERSONALIZADO (SOLUCIÓN)
        ->filter(function ($query) use ($request) {

            if ($request->has('search') && $request->search['value'] != '') {

                $search = strtolower($request->search['value']);

                $query->where(function ($q) use ($search) {

                    $q->whereRaw('LOWER(libros.titulo) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(libros.isbn) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(libros.codigo) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(libros.codigo_dewey) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(libros.estado) LIKE ?', ["%{$search}%"])

                    //  AUTORES (RELACIÓN)
                    ->orWhereHas('autores', function ($q2) use ($search) {
                        $q2->whereRaw('LOWER(nombres) LIKE ?', ["%{$search}%"]);
                    })

                    //  TIPO REGISTRO
                    ->orWhereHas('tipo_registro', function ($q3) use ($search) {
                        $q3->whereRaw('LOWER(nombres) LIKE ?', ["%{$search}%"]);
                    });

                });
            }
        })

        //  COLUMNA AUTORES (VISIBLE EN TABLA)
        ->addColumn('autores', function($row){
            return $row->autores->pluck('nombres')->join(', ');
        })

        ->addColumn('acciones', function($row){
            return '
                <div class="dropdown admin-action-menu">
                    <button class="btn admin-action-menu__trigger" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Abrir acciones">
                        <i class="bi bi-three-dots"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end admin-action-menu__dropdown">
                        <a class="dropdown-item admin-action-link admin-action-link--view verEjemplares" href="/administracion/ejemplares/'.$row->id.'">
                            <i class="bi bi-eye"></i><span>Ver</span>
                        </a>
                        <a class="dropdown-item admin-action-link admin-action-link--edit editarLibro" href="/administracion/libros_editar/'.$row->id.'">
                            <i class="bi bi-pencil-square"></i><span>Editar</span>
                        </a>
                        <button class="dropdown-item admin-action-link admin-action-link--delete eliminarLibro" data-id="'.$row->id.'">
                            <i class="bi bi-trash3"></i><span>Eliminar</span>
                        </button>
                    </div>
                </div>
            ';
        })

        ->rawColumns(['acciones'])
        ->make(true);
    }

    public function buscar(Request $request)
    {
        $search = $request->get('q');
        $libros = Libro::with(['autores','editorial'])
            ->when($search, function ($query) use ($search) {
                $query->where('titulo', 'like', "%{$search}%");
            })
            ->limit(20)
            ->get();
        return response()->json(
            $libros->map(function ($libro) {
                return [
                    'id' => $libro->id,
                    'text' => $libro->titulo,
                    // autores como array
                    'autores' => $libro->autores->map(function($autor){
                        return [
                            'id' => $autor->id,
                            'nombre' => $autor->nombres.' '.$autor->apellidos
                        ];
                    }),
                    'editorial' => $libro->editorial
                        ? [
                            'id' => $libro->editorial->id,
                            'nombre' => $libro->editorial->nombre
                        ]
                        : null,

                    'imagen' => $libro->imagen_url,
                ];
            })
        );
    }
    public function nuevo(Request $request){

        $request->validate([
            'titulo' => 'required',
            'tipo_registro_id' => 'required',
            'codigo' => 'required',
            'codigo_dewey' => 'required'
        ]);

        DB::beginTransaction();

        try {

            $codigoDewey = $this->resolverCodigoDewey($request->codigo_dewey);
            $libro = new Libro();
            $libro->isbn = $request->isbn;
            $libro->tipo_registro_id = $request->tipo_registro_id;
            $libro->codigo_dewey = $codigoDewey;
            $libro->codigo = $request->codigo;
            $libro->titulo = $request->titulo;
            $libro->editorial_id = $request->editorial_id;
            $libro->edicion = $request->edicion;
            $libro->anio_edicion = $request->anio_edicion;
            $libro->idioma = $request->idioma;
            $libro->paginas = $request->paginas;
            $libro->fecha_publicacion = $request->fecha_publicacion;
            $libro->lugar_publicacion = $request->lugar_publicacion;
            $libro->resumen = $request->resumen;
            $libro->palabras_clave = $request->palabras_clave;
            $libro->anotaciones = $request->anotaciones;
            $libro->anotaciones = $request->anotaciones;

            // subir imagen
            if($request->hasFile('imagen')){
                $archivo = $request->file('imagen');
                $nombre = time().'_'.$archivo->getClientOriginalName();
                $archivo->storeAs('libros',$nombre,'public');
                $libro->imagen = 'storage/libros/'.$nombre;
            }

            // subir pdf
            if($request->hasFile('archivo_indice')){
                $archivo = $request->file('archivo_indice');
                $nombre = time().'_'.$archivo->getClientOriginalName();
                $archivo->storeAs('indices', $nombre, 'public');
                $libro->archivo_indice = 'storage/indices/'.$nombre;
            }

            $libro->save();

            // autores
            if($request->autor_id){
                $libro->autores()->sync($request->autor_id);
            }

            // materias
            if($request->materias){
                $libro->materias()->sync($request->materias);
            }

            self::guardarAprendizaje($request->titulo, $codigoDewey);

            DB::commit();

            return response()->json([
                'success'=>true
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success'=>false,
                'mensaje'=>$e->getMessage()
            ],500);
        }
    }
    public function actualizar(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'titulo' => 'required',
            'tipo_registro_id' => 'required',
            'codigo' => 'required',
            'codigo_dewey' => 'required'
        ]);

        $libro = Libro::find($request->id);

        if(!$libro){
            return response()->json([
                'success'=>false,
                'message'=>'Libro no encontrado'
            ],404);
        }

        // 🔥 guardar valor anterior
        $codigoAnterior = $libro->codigo_dewey;

        // ================= ACTUALIZAR =================
        $libro->isbn = $request->isbn;
        $libro->tipo_registro_id = $request->tipo_registro_id;
        $libro->codigo_dewey = $this->resolverCodigoDewey($request->codigo_dewey);
        $libro->codigo = $request->codigo;
        $libro->titulo = $request->titulo;
        $libro->editorial_id = $request->editorial_id;
        $libro->edicion = $request->edicion;
        $libro->anio_edicion = $request->anio_edicion;
        $libro->idioma = $request->idioma;
        $libro->paginas = $request->paginas;
        $libro->fecha_publicacion = $request->fecha_publicacion;
        $libro->lugar_publicacion = $request->lugar_publicacion;
        $libro->palabras_clave = $request->palabras_clave;
        $libro->resumen = $request->resumen;
        $libro->anotaciones = $request->anotaciones;

        // ================= IMAGEN =================
        if($request->hasFile('imagen')){
            if($libro->imagen){
                Storage::delete('public/libros/'.$libro->imagen);
            }

            $archivo = $request->file('imagen');
            $nombre = time().'_'.$archivo->getClientOriginalName();
            $archivo->storeAs('public/libros',$nombre);

            $libro->imagen = $nombre;
        }

        // ================= PDF =================
        if($request->hasFile('archivo_indice')){
            if($libro->archivo_indice){
                Storage::delete('public/indices/'.$libro->archivo_indice);
            }

            $archivo = $request->file('archivo_indice');
            $nombre = time().'_'.$archivo->getClientOriginalName();
            $archivo->storeAs('public/indices',$nombre);

            $libro->archivo_indice = $nombre;
        }

        $libro->save();

        DB::table('ejemplares')
            ->where('libro_id', $libro->id)
            ->update([
                'codigo_dewey' => $libro->codigo_dewey . $libro->codigo,
            ]);

        // ================= RELACIONES =================
        $libro->autores()->sync($request->autor_id ?? []);
        $libro->materias()->sync($request->materias ?? []);

        // ================= 🔥 APRENDIZAJE =================
        $this->guardarAprendizaje(
            $request->titulo,
            $request->codigo_dewey,
            $codigoAnterior
        );

        return response()->json([
            'success'=>true,
            'message'=>'Libro actualizado correctamente'
        ]);
    }

    public function sugerirDewey(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|min:3',
        ]);

        $prediccion = $this->predecirDewey($request->titulo);

        if (!$prediccion) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró una sugerencia confiable para el título enviado.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'codigo' => $prediccion['codigo'],
            'nombre' => $prediccion['nombre'],
            'score' => $prediccion['score'],
            'coincidencias' => $prediccion['coincidencias'],
        ]);
    }

    public function generarCodigo(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|min:2',
            'codigo_dewey' => 'required',
            'autor_id' => 'required|array|min:1',
        ]);

        $codigoDewey = $this->resolverCodigoDewey($request->codigo_dewey);
        $autorId = collect($request->autor_id)->filter()->first();
        $autor = DB::table('autores')->where('id', $autorId)->first();

        if (!$autor) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo identificar el autor principal para generar el codigo.',
            ], 422);
        }

        $apellido = trim((string) ($autor->apellidos ?? ''));
        $apellidoBase = trim(strtok($apellido, ' ') ?: $apellido);
        $nombreAutor = trim((string) ($autor->nombres ?? ''));
        $titulo = trim((string) $request->titulo);
        $edicion = trim((string) ($request->edicion ?? ''));

        $cutter = $this->buscarCodigoCutterPorApellido($apellidoBase);
        $apellidoNormalizado = $this->normalizarClave($apellidoBase);
        $nombreNormalizado = $this->normalizarClave($nombreAutor);
        $tituloNormalizado = $this->normalizarClave($titulo);
        $nombreInicial = $this->primeraLetra($nombreAutor);
        $tituloInicial = $this->primeraLetra($titulo);
        $prefijoEdicion = $this->prefijoEdicion($edicion);

        $librosMismoDewey = Libro::with(['autores' => function ($query) {
            $query->orderBy('apellidos')->orderBy('nombres')->orderBy('autores.id');
        }])
            ->where('codigo_dewey', $codigoDewey)
            ->when($request->id, fn ($query) => $query->where('id', '!=', $request->id))
            ->get();

        $mismoCutter = $librosMismoDewey->filter(function ($libro) use ($cutter) {
            $autor = $libro->autores->sortBy([['apellidos', 'asc'], ['nombres', 'asc'], ['id', 'asc']])->first();
            $apellido = trim((string) optional($autor)->apellidos);
            $apellidoBase = trim(strtok($apellido, ' ') ?: $apellido);

            return $this->buscarCodigoCutterPorApellido($apellidoBase) === $cutter;
        });

        $prefijoAutor = $mismoCutter->isNotEmpty() ? $nombreInicial : '';

        $mismoAutorInicial = $mismoCutter->filter(function ($libro) use ($prefijoAutor) {
            $autor = $libro->autores->sortBy([['apellidos', 'asc'], ['nombres', 'asc'], ['id', 'asc']])->first();
            return $this->primeraLetra(trim((string) optional($autor)->nombres)) === $prefijoAutor;
        });

        $prefijoTitulo = $mismoAutorInicial->isNotEmpty() ? $tituloInicial : '';

        $mismoTituloInicial = $mismoAutorInicial->filter(function ($libro) use ($prefijoTitulo) {
            return $this->primeraLetra($libro->titulo) === $prefijoTitulo;
        });

        $prefijoEdicion = $mismoTituloInicial->isNotEmpty() ? $prefijoEdicion : '';

        $codigoBase = strtoupper($cutter . $prefijoAutor . $prefijoTitulo . $prefijoEdicion);
        if ($codigoBase === '') {
            $codigoBase = '000';
        }

        $codigo = $codigoBase;
        $contador = 2;
        $usados = Libro::query()
            ->whereNotNull('codigo')
            ->when($request->id, fn ($query) => $query->where('id', '!=', $request->id))
            ->pluck('codigo')
            ->map(fn ($item) => strtoupper((string) $item))
            ->flip()
            ->all();

        while (isset($usados[$codigo])) {
            $codigo = $codigoBase . $contador;
            $contador++;
        }

        return response()->json([
            'success' => true,
            'codigo' => $codigo,
            'detalle' => [
                'cutter' => $cutter,
                'prefijo_autor' => $prefijoAutor,
                'prefijo_titulo' => $prefijoTitulo,
                'prefijo_edicion' => $prefijoEdicion,
            ],
        ]);
    }

     public static function guardarAprendizaje($texto, $codigo, $codigoAnterior = null)
    {
        if (!$texto || !$codigo) return;

        $textoNormalizado = mb_strtolower($texto, 'UTF-8');
        $textoNormalizado = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $textoNormalizado) ?: $textoNormalizado;
        $textoNormalizado = preg_replace('/[^a-z0-9\s]/', ' ', $textoNormalizado);

        $stopwords = [
            'de', 'la', 'el', 'y', 'en', 'los', 'las', 'un', 'una', 'por', 'para',
            'con', 'del', 'al', 'a', 'o', 'u', 'que', 'se', 'su', 'sus', 'como',
        ];

        $tokens = preg_split('/\s+/', trim($textoNormalizado), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $tokens = array_values(array_unique(array_filter($tokens, function ($token) use ($stopwords) {
            return !in_array($token, $stopwords, true) && strlen($token) > 2;
        })));

        foreach ($tokens as $token) {
            $apr = Dewey_aprendizaje::where('palabra', $token)
                ->where('codigo_dewey', $codigo)
                ->first();

            if ($apr) {
                $apr->increment('peso');
            } else {
                Dewey_aprendizaje::create([
                    'palabra' => $token,
                    'codigo_dewey' => $codigo,
                    'peso' => 1
                ]);
            }
        }

        return;

        $apr = Dewey_aprendizaje::where('palabra', $texto)
            ->where('codigo_dewey', $codigo)
            ->first();

        if ($apr) {
            // 🔥 ya existe → aumenta peso
            $apr->increment('peso');
        } else {
            // 🔥 nuevo aprendizaje
            Dewey_aprendizaje::create([
                'palabra' => $texto,
                'codigo_dewey' => $codigo,
                'peso' => 1
            ]);
        }
    }

    private function resolverCodigoDewey($valor): string
    {
        if ($valor === null || $valor === '') {
            return '';
        }

        $dewey = Dewey::query()
            ->where('id', $valor)
            ->orWhere('codigo', $valor)
            ->first();

        return (string) ($dewey->codigo ?? $valor);
    }

    private function normalizarPalabras(?string $texto): array
    {
        if (!$texto) {
            return [];
        }

        $texto = mb_strtolower($texto, 'UTF-8');
        $texto = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto) ?: $texto;
        $texto = preg_replace('/[^a-z0-9\s]/', ' ', $texto);
        $palabras = preg_split('/\s+/', $texto, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_values(array_unique(array_filter($palabras, function ($palabra) {
            return !in_array($palabra, $this->stopwords, true) && strlen($palabra) > 2;
        })));
    }

    private function predecirDewey(?string $titulo): ?array
    {
        $palabrasLibro = $this->normalizarPalabras($titulo);

        if (empty($palabrasLibro)) {
            return null;
        }

        $deweys = Dewey::all()->map(function ($dewey) {
            $tokens = $this->normalizarPalabras(trim(($dewey->nombre ?? '') . ' ' . ($dewey->keywords ?? '')));

            return [
                'codigo' => $dewey->codigo,
                'nombre' => $dewey->nombre,
                'nivel' => (int) ($dewey->nivel ?? 0),
                'keywords' => $tokens,
            ];
        });

        $aprendizaje = Dewey_aprendizaje::query()
            ->whereIn('palabra', $palabrasLibro)
            ->get()
            ->groupBy('codigo_dewey');

        $mejorCoincidencia = null;
        $mejorScore = 0.0;
        $mejoresCoincidencias = [];

        foreach ($deweys as $dewey) {
            if (empty($dewey['keywords'])) {
                continue;
            }

            $coincidenciasBase = array_values(array_intersect($palabrasLibro, $dewey['keywords']));
            $numCoincidencias = count($coincidenciasBase);

            $precision = $numCoincidencias > 0 ? $numCoincidencias / max(count($dewey['keywords']), 1) : 0;
            $recall = $numCoincidencias > 0 ? $numCoincidencias / max(count($palabrasLibro), 1) : 0;
            $scoreBase = ($precision + $recall) > 0 ? (2 * $precision * $recall) / ($precision + $recall) : 0;

            $pesoAprendizaje = (float) ($aprendizaje[$dewey['codigo']] ?? collect())->sum('peso');
            $score = $scoreBase + min($pesoAprendizaje * 0.08, 0.8) + ($dewey['nivel'] * 0.05);

            if ($score > $mejorScore) {
                $mejorScore = $score;
                $mejorCoincidencia = $dewey;
                $mejoresCoincidencias = $coincidenciasBase;
            }
        }

        if (!$mejorCoincidencia || $mejorScore < 0.2) {
            return null;
        }

        return [
            'codigo' => $mejorCoincidencia['codigo'],
            'nombre' => $mejorCoincidencia['nombre'],
            'score' => round($mejorScore, 4),
            'coincidencias' => $mejoresCoincidencias,
        ];
    }

    private function normalizarClave(?string $texto): string
    {
        if (!$texto) {
            return '';
        }

        $texto = mb_strtolower($texto, 'UTF-8');
        $texto = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto) ?: $texto;
        $texto = preg_replace('/[^a-z0-9]/', '', $texto);

        return strtoupper($texto);
    }

    private function prefijoUnico(string $texto, array $universo): string
    {
        $texto = $this->normalizarClave($texto);
        $universo = array_values(array_filter(array_map(fn ($item) => $this->normalizarClave($item), $universo)));

        if ($texto === '') {
            return '';
        }

        for ($longitud = 1; $longitud <= strlen($texto); $longitud++) {
            $prefijo = substr($texto, 0, $longitud);
            $coincidencias = array_filter($universo, fn ($item) => str_starts_with($item, $prefijo));

            if (count($coincidencias) === 1) {
                return $prefijo;
            }
        }

        return $texto;
    }

    private function prefijoTituloLimitado(string $texto, array $universo): string
    {
        $texto = $this->normalizarClave($texto);
        $universo = array_values(array_filter(array_map(fn ($item) => $this->normalizarClave($item), $universo)));

        if ($texto === '') {
            return '';
        }

        $primerCaracter = substr($texto, 0, 1);
        $coincidenciasPrimerCaracter = array_filter($universo, fn ($item) => str_starts_with($item, $primerCaracter));

        if (count($coincidenciasPrimerCaracter) <= 1 || strlen($texto) === 1) {
            return $primerCaracter;
        }

        return substr($texto, 0, min(2, strlen($texto)));
    }

    private function primeraLetra(?string $texto): string
    {
        $texto = $this->normalizarClave($texto);

        return $texto === '' ? '' : substr($texto, 0, 1);
    }

    private function prefijoEdicion(?string $edicion): string
    {
        $edicion = $this->normalizarClave($edicion);

        if ($edicion === '') {
            return '';
        }

        preg_match_all('/[0-9]+/', $edicion, $coincidencias);
        if (!empty($coincidencias[0])) {
            return implode('', $coincidencias[0]);
        }

        return substr($edicion, 0, min(2, strlen($edicion)));
    }

    private function buscarCodigoCutterPorApellido(?string $apellido): string
    {
        $apellidoNormalizado = $this->normalizarClave($apellido);

        if ($apellidoNormalizado === '') {
            return '000';
        }

        $raiz = substr($apellidoNormalizado, 0, 3);
        $columna = Schema::hasColumn('codido_cutters', 'nombre') ? 'nombre' : 'valor';

        $registros = DB::table('codido_cutters')
            ->select('codigo', $columna . ' as clave')
            ->get()
            ->map(function ($registro) {
                return [
                    'codigo' => (string) $registro->codigo,
                    'clave' => $this->normalizarClave($registro->clave),
                ];
            })
            ->filter(fn ($registro) => $registro['clave'] !== '')
            ->sortBy(fn ($registro) => strlen($registro['clave']))
            ->values();

        foreach ($registros as $registro) {
            if ($registro['clave'] === $raiz || str_starts_with($registro['clave'], $raiz)) {
                return $registro['codigo'];
            }
        }

        return $raiz;
    }
}
