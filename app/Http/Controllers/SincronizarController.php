<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use App\Models\CutterAprendizaje;
use App\Models\Libro;
use App\Models\Dewey;
use App\Models\Dewey_aprendizaje;
 
class SincronizarController extends Controller
{
    private array $stopwords = [
        'de', 'la', 'el', 'y', 'en', 'los', 'las', 'un', 'una', 'por', 'para',
        'con', 'del', 'al', 'a', 'o', 'u', 'que', 'se', 'su', 'sus', 'como',
    ];
    private array $syncStats = [];
    private ?array $userSyncMap = null;

    private function resetSyncStats(): void
    {
        $this->syncStats = [];
    }

    private function addSyncStat(string $tabla, int $insertados = 0, int $omitidos = 0, array $motivos = []): void
    {
        if (! isset($this->syncStats[$tabla])) {
            $this->syncStats[$tabla] = [
                'insertados' => 0,
                'omitidos' => 0,
                'motivos' => [],
            ];
        }

        $this->syncStats[$tabla]['insertados'] += $insertados;
        $this->syncStats[$tabla]['omitidos'] += $omitidos;

        foreach ($motivos as $motivo => $cantidad) {
            $this->syncStats[$tabla]['motivos'][$motivo] =
                ($this->syncStats[$tabla]['motivos'][$motivo] ?? 0) + $cantidad;
        }
    }

    private function resolveUserSyncMap(): array
    {
        if ($this->userSyncMap !== null) {
            return $this->userSyncMap;
        }

        $localUsers = DB::table('users')
            ->select('id', 'persona_id', 'email', 'name')
            ->get();

        $byId = [];
        $byPersona = [];
        $byEmail = [];
        $byName = [];

        foreach ($localUsers as $user) {
            $byId[$user->id] = $user->id;

            if (! empty($user->persona_id)) {
                $byPersona[$user->persona_id] = $user->id;
            }

            if (! empty($user->email)) {
                $byEmail[mb_strtolower(trim($user->email), 'UTF-8')] = $user->id;
            }

            if (! empty($user->name)) {
                $byName[mb_strtoupper(trim($user->name), 'UTF-8')] = $user->id;
            }
        }

        $map = [];
        DB::connection('mysql2')->table('users')
            ->select('id', 'persona_id', 'email', 'nombre as name')
            ->orderBy('id')
            ->chunk(500, function ($rows) use (&$map, $byId, $byPersona, $byEmail, $byName) {
                foreach ($rows as $row) {
                    $resolved = $byId[$row->id] ?? null;

                    if ($resolved === null && ! empty($row->persona_id)) {
                        $resolved = $byPersona[$row->persona_id] ?? null;
                    }

                    if ($resolved === null && ! empty($row->email)) {
                        $resolved = $byEmail[mb_strtolower(trim($row->email), 'UTF-8')] ?? null;
                    }

                    if ($resolved === null && ! empty($row->name)) {
                        $resolved = $byName[mb_strtoupper(trim($row->name), 'UTF-8')] ?? null;
                    }

                    if ($resolved !== null) {
                        $map[$row->id] = $resolved;
                    }
                }
            });

        return $this->userSyncMap = $map;
    }

    function limpiarTexto($texto) {
        $texto = strtolower($texto);
        $texto = preg_replace('/[^a-z0-9\s]/', '', $texto); // elimina caracteres especiales
        return $texto;
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
            $tokens = $this->normalizarPalabras(
                trim(($dewey->nombre ?? '') . ' ' . ($dewey->keywords ?? ''))
            );

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

            $precision = $numCoincidencias > 0
                ? $numCoincidencias / max(count($dewey['keywords']), 1)
                : 0;
            $recall = $numCoincidencias > 0
                ? $numCoincidencias / max(count($palabrasLibro), 1)
                : 0;

            $scoreBase = ($precision + $recall) > 0
                ? (2 * $precision * $recall) / ($precision + $recall)
                : 0;

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

    public function clasificarLibrosMasivos()
    {
        ini_set('max_execution_time', '0');
        set_time_limit(0);

        $deweys = Dewey::all()->map(function ($dewey) {
            $tokens = $this->normalizarPalabras(
                trim(($dewey->nombre ?? '') . ' ' . ($dewey->keywords ?? ''))
            );

            return [
                'codigo' => $dewey->codigo,
                'nivel' => (int) ($dewey->nivel ?? 0),
                'keywords' => $tokens,
            ];
        })->values()->all();

        $aprendizajePorPalabra = [];
        Dewey_aprendizaje::query()
            ->select('palabra', 'codigo_dewey', 'peso')
            ->orderBy('palabra')
            ->chunk(2000, function ($rows) use (&$aprendizajePorPalabra) {
                foreach ($rows as $row) {
                    $palabra = $this->normalizarClave($row->palabra);

                    if ($palabra === '') {
                        continue;
                    }

                    $aprendizajePorPalabra[$palabra][$row->codigo_dewey] =
                        ($aprendizajePorPalabra[$palabra][$row->codigo_dewey] ?? 0) + (float) $row->peso;
                }
            });

        Libro::select('id', 'titulo')->chunk(500, function ($libros) use ($deweys, $aprendizajePorPalabra) {
            $updates = [];

            foreach ($libros as $libro) {
                $palabrasLibro = $this->normalizarPalabras($libro->titulo);

                if (empty($palabrasLibro)) {
                    continue;
                }

                $pesosAprendizaje = [];
                foreach ($palabrasLibro as $palabra) {
                    $clave = $this->normalizarClave($palabra);

                    foreach (($aprendizajePorPalabra[$clave] ?? []) as $codigo => $peso) {
                        $pesosAprendizaje[$codigo] = ($pesosAprendizaje[$codigo] ?? 0) + $peso;
                    }
                }

                $mejorCodigo = null;
                $mejorScore = 0.0;

                foreach ($deweys as $dewey) {
                    if (empty($dewey['keywords'])) {
                        continue;
                    }

                    $coincidencias = array_intersect($palabrasLibro, $dewey['keywords']);
                    $numCoincidencias = count($coincidencias);

                    $precision = $numCoincidencias > 0
                        ? $numCoincidencias / max(count($dewey['keywords']), 1)
                        : 0;
                    $recall = $numCoincidencias > 0
                        ? $numCoincidencias / max(count($palabrasLibro), 1)
                        : 0;

                    $scoreBase = ($precision + $recall) > 0
                        ? (2 * $precision * $recall) / ($precision + $recall)
                        : 0;

                    $pesoAprendizaje = (float) ($pesosAprendizaje[$dewey['codigo']] ?? 0);
                    $score = $scoreBase + min($pesoAprendizaje * 0.08, 0.8) + ($dewey['nivel'] * 0.05);

                    if ($score > $mejorScore) {
                        $mejorScore = $score;
                        $mejorCodigo = $dewey['codigo'];
                    }
                }

                if ($mejorCodigo && $mejorScore >= 0.2) {
                    $updates[] = [
                        'id' => $libro->id,
                        'codigo_dewey' => $mejorCodigo,
                    ];
                }
            }

            if (!empty($updates)) {
                $ids = collect($updates)->pluck('id')->toArray();

                $cases = '';
                foreach ($updates as $update) {
                    $codigoDewey = addslashes($update['codigo_dewey']);
                    $cases .= "WHEN {$update['id']} THEN '{$codigoDewey}' ";
                }

                DB::update("
                    UPDATE libros
                    SET codigo_dewey = CASE id
                        $cases
                    END
                    WHERE id IN (" . implode(',', $ids) . ")
                ");
            }
        });

        return 'Clasificacion masiva optimizada completada';

        Libro::chunk(500, function ($libros) {
            $updates = [];

            foreach ($libros as $libro) {
                $prediccion = $this->predecirDewey($libro->titulo);

                if ($prediccion) {
                    $updates[] = [
                        'id' => $libro->id,
                        'codigo_dewey' => $prediccion['codigo'],
                    ];
                }
            }

            if (!empty($updates)) {
                $ids = collect($updates)->pluck('id')->toArray();

                $cases = '';
                foreach ($updates as $update) {
                    $cases .= "WHEN {$update['id']} THEN '{$update['codigo_dewey']}' ";
                }

                DB::update("
                    UPDATE libros
                    SET codigo_dewey = CASE id
                        $cases
                    END
                    WHERE id IN (" . implode(',', $ids) . ")
                ");
            }
        });

        return 'Clasificacion masiva optimizada completada';

        // STOPWORDS bÃ¡sicas (puedes ampliarlas)
        $stopwords = ['de','la','el','y','en','los','las','un','una','por','para','con','del','al'];

        // Normalizador reutilizable
        $normalizar = function($texto) use ($stopwords) {
            $texto = strtolower($texto);
            $texto = preg_replace('/[^a-z0-9\s]/', '', $texto);

            $palabras = array_filter(explode(' ', $texto), function($p) use ($stopwords) {
                return !in_array($p, $stopwords) && strlen($p) > 2;
            });

            return array_values(array_unique($palabras));
        };

        // Cargar Dewey optimizado
        $deweys = Dewey::all()->map(function($d) use ($normalizar) {
            $keywords = explode(',', strtolower($d->keywords));
            $d->keywords = $normalizar(implode(' ', $keywords));
            return $d;
        });

        Libro::chunk(500, function($libros) use ($deweys, $normalizar) {

            $updates = [];

            foreach ($libros as $libro) {

                $palabrasLibro = $normalizar($libro->titulo);

                if (empty($palabrasLibro)) continue;

                $mejorCoincidencia = null;
                $mejorScore = 0;

                foreach ($deweys as $d) {

                    if (empty($d->keywords)) continue;

                    $coincidencias = array_intersect($palabrasLibro, $d->keywords);
                    $numCoincidencias = count($coincidencias);

                    if ($numCoincidencias === 0) continue;

                    // ðŸŽ¯ SCORE PROFESIONAL (tipo TF simple)
                    $precision = $numCoincidencias / count($d->keywords);
                    $recall    = $numCoincidencias / count($palabrasLibro);

                    // F1 Score balanceado
                    $score = (2 * $precision * $recall) / max(($precision + $recall), 0.0001);

                    // Bonus por nivel (ajusta peso)
                    $score += ($d->nivel * 0.05);

                    if ($score > $mejorScore) {
                        $mejorScore = $score;
                        $mejorCoincidencia = $d;
                    }
                }

                // ðŸ”’ Umbral mÃ­nimo (evita clasificaciones malas)
                if ($mejorCoincidencia && $mejorScore >= 0.2) {
                    $updates[] = [
                        'id' => $libro->id,
                        'codigo_dewey' => $mejorCoincidencia->codigo
                    ];
                }
            }

            // âš¡ UPDATE MASIVO (MUCHO mÃ¡s eficiente)
            if (!empty($updates)) {

                $ids = collect($updates)->pluck('id')->toArray();

                $cases = "";
                foreach ($updates as $u) {
                    $cases .= "WHEN {$u['id']} THEN '{$u['codigo_dewey']}' ";
                }

                DB::update("
                    UPDATE libros
                    SET codigo_dewey = CASE id
                        $cases
                    END
                    WHERE id IN (" . implode(',', $ids) . ")
                ");
            }
        });

        return "ClasificaciÃ³n masiva optimizada completada âœ…";
    }



    public function obtenerDeweyPorTitulo(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|min:3',
        ]);

        $prediccion = $this->predecirDewey($request->titulo);

        if (!$prediccion) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontro una sugerencia confiable.',
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
        $aprendido = $this->buscarCodigoCutterAprendidoPorApellido($apellido);

        if ($aprendido !== null && $aprendido !== '') {
            return $aprendido;
        }

        return $this->buscarCodigoCutterBasePorApellido($apellido);
    }

    private function buscarCodigoCutterAprendidoPorApellido(?string $apellido): ?string
    {
        if (!Schema::hasTable('cutter_aprendizajes')) {
            return null;
        }

        $apellidoNormalizado = $this->normalizarClave($apellido);

        if ($apellidoNormalizado === '') {
            return null;
        }

        $raiz = substr($apellidoNormalizado, 0, 3);

        return CutterAprendizaje::query()
            ->where(function ($query) use ($apellidoNormalizado, $raiz) {
                $query->where('clave_autor', $apellidoNormalizado)
                    ->orWhere('clave_autor', 'like', $raiz . '%');
            })
            ->get()
            ->sortByDesc(function ($aprendizaje) use ($apellidoNormalizado) {
                return [
                    $aprendizaje->clave_autor === $apellidoNormalizado ? 1 : 0,
                    (int) $aprendizaje->peso,
                    strlen((string) $aprendizaje->clave_autor),
                ];
            })
            ->pluck('codigo_cutter')
            ->filter()
            ->map(fn ($codigo) => strtoupper((string) $codigo))
            ->first();
    }

    private function buscarCodigoCutterBasePorApellido(?string $apellido): string
    {
        $apellidoNormalizado = $this->normalizarClave($apellido);

        if ($apellidoNormalizado === '') {
            return '000';
        }

        $raiz = substr($apellidoNormalizado, 0, 3);
        static $cutterExactos = null;
        static $cutterPorPrefijo = null;

        if ($cutterExactos === null || $cutterPorPrefijo === null) {
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

            $cutterExactos = [];
            $cutterPorPrefijo = $registros->all();

            foreach ($cutterPorPrefijo as $registro) {
                $cutterExactos[$registro['clave']] = $registro['codigo'];
            }
        }

        $codigo = $cutterExactos[$raiz] ?? null;

        if ($codigo) {
            return $codigo;
        }

        foreach ($cutterPorPrefijo as $registro) {
            if (str_starts_with($registro['clave'], $raiz)) {
                return $registro['codigo'];
            }
        }

        return $raiz;
    }

    public function actualizarCodigosTopograficos()
    {
        ini_set('max_execution_time', '0');
        set_time_limit(0);

        $libros = Libro::with(['autores' => function ($query) {
            $query->orderBy('apellidos')->orderBy('nombres')->orderBy('autores.id');
        }])
            ->whereNotNull('codigo_dewey')
            ->where('codigo_dewey', '!=', '')
            ->get();

        $data = $libros->map(function ($libro) {
            $autorPrincipal = $libro->autores->sortBy([
                ['apellidos', 'asc'],
                ['nombres', 'asc'],
                ['id', 'asc'],
            ])->first();

            $apellido = trim((string) optional($autorPrincipal)->apellidos);
            $apellidoBase = trim(strtok($apellido, ' ') ?: $apellido);
            $nombreAutor = trim((string) optional($autorPrincipal)->nombres);

            return [
                'libro' => $libro,
                'dewey' => (string) $libro->codigo_dewey,
                'autor_id' => optional($autorPrincipal)->id,
                'apellido' => $apellidoBase,
                'apellido_normalizado' => $this->normalizarClave($apellidoBase),
                'nombre_normalizado' => $this->normalizarClave($nombreAutor),
                'nombre_inicial' => $this->primeraLetra($nombreAutor),
                'edicion_normalizada' => $this->prefijoEdicion($libro->edicion),
                'titulo_normalizado' => $this->normalizarClave($libro->titulo),
                'titulo_inicial' => $this->primeraLetra($libro->titulo),
                'cutter' => $this->buscarCodigoCutterPorApellido($apellidoBase),
            ];
        })->values();

        $actualizaciones = [];
        $idsLibros = $data->pluck('libro.id')->all();
        $codigosUsados = Libro::query()
            ->whereNotNull('codigo')
            ->where('codigo', '!=', '')
            ->whereNotIn('id', $idsLibros)
            ->pluck('codigo')
            ->map(fn ($codigo) => strtoupper((string) $codigo))
            ->flip()
            ->map(fn () => true)
            ->all();

        foreach ($data->groupBy('dewey') as $dewey => $grupoDewey) {
            foreach ($grupoDewey->groupBy('cutter') as $grupoCutter) {
                foreach ($grupoCutter as $item) {
                    $prefijoAutor = $grupoCutter->count() > 1 ? ($item['nombre_inicial'] ?? '') : '';

                    $grupoMismaInicialAutor = $grupoCutter->filter(function ($comparado) use ($prefijoAutor) {
                        return ($comparado['nombre_inicial'] ?? '') === $prefijoAutor;
                    });

                    $prefijoTitulo = $grupoMismaInicialAutor->count() > 1 ? ($item['titulo_inicial'] ?? '') : '';

                    $grupoMismoTitulo = $grupoMismaInicialAutor->filter(function ($comparado) use ($prefijoTitulo) {
                        return ($comparado['titulo_inicial'] ?? '') === $prefijoTitulo;
                    });

                    $prefijoEdicion = $grupoMismoTitulo->count() > 1
                        ? ($item['edicion_normalizada'] ?? '')
                        : '';

                    $codigoLibro = strtoupper($item['cutter'] . $prefijoAutor . $prefijoTitulo . $prefijoEdicion);

                    if ($codigoLibro === '') {
                        $codigoLibro = '000';
                    }

                    $contador = 2;
                    $codigoBase = $codigoLibro;
                    while (isset($codigosUsados[$codigoLibro])) {
                        $codigoLibro = $codigoBase . $contador;
                        $contador++;
                    }

                    $codigosUsados[$codigoLibro] = true;

                    $actualizaciones[] = [
                        'id' => $item['libro']->id,
                        'codigo' => $codigoLibro,
                        'codigo_completo' => $dewey . $codigoLibro,
                    ];
                }
            }
        }

        DB::transaction(function () use ($actualizaciones) {
            foreach (array_chunk($actualizaciones, 500) as $bloque) {
                $ids = array_column($bloque, 'id');

                $casesTemporales = '';
                foreach ($bloque as $actualizacion) {
                    $casesTemporales .= "WHEN {$actualizacion['id']} THEN '__TMP__{$actualizacion['id']}' ";
                }

                DB::update("
                    UPDATE libros
                    SET codigo = CASE id
                        $casesTemporales
                    END
                    WHERE id IN (" . implode(',', $ids) . ")
                ");

                $casesCodigo = '';
                $casesEjemplares = '';

                foreach ($bloque as $actualizacion) {
                    $codigo = addslashes($actualizacion['codigo']);
                    $codigoCompleto = addslashes($actualizacion['codigo_completo']);
                    $casesCodigo .= "WHEN {$actualizacion['id']} THEN '{$codigo}' ";
                    $casesEjemplares .= "WHEN {$actualizacion['id']} THEN '{$codigoCompleto}' ";
                }

                DB::update("
                    UPDATE libros
                    SET codigo = CASE id
                        $casesCodigo
                    END
                    WHERE id IN (" . implode(',', $ids) . ")
                ");

                DB::update("
                    UPDATE ejemplares
                    SET codigo_dewey = CASE libro_id
                        $casesEjemplares
                    END
                    WHERE libro_id IN (" . implode(',', $ids) . ")
                ");
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Codigos topograficos actualizados correctamente.',
            'actualizados' => count($actualizaciones),
        ]);
    }

    private function mapearDewey($categorias)
    {
        $map = [
            'Computers' => '004',
            'Programming' => '005',
            'Science' => '500',
            'Mathematics' => '510',
            'History' => '900',
            'Literature' => '800',
            'Education' => '370',
            'Law' => '340',
        ];

        foreach ($categorias as $cat) {
            foreach ($map as $key => $dewey) {
                if (stripos($cat, $key) !== false) {
                    return $dewey;
                }
            }
        }

        return '000'; // desconocido
    }

    public function sincronizar()
    {
        DB::beginTransaction();

        try {
            $this->resetSyncStats();

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            $this->ejecutarPaso('paises', fn() => $this->paises());
            $this->ejecutarPaso('editoriales', fn() => $this->editoriales());
            $this->ejecutarPaso('autores', fn() => $this->autores());
            $this->ejecutarPaso('materias', fn() => $this->materias());
            $this->ejecutarPaso('carreras', fn() => $this->carreras());
            $this->ejecutarPaso('personas', fn() => $this->personas());
            $this->ejecutarPaso('usuarios', fn() => $this->usuarios());
            $this->ejecutarPaso('actividad_categorias', fn() => $this->actividadCategorias());
            $this->ejecutarPaso('actividades', fn() => $this->actividades());
            $this->ejecutarPaso('libros', fn() => $this->libros());
            $this->ejecutarPaso('relaciones', fn() => $this->libro_relaciones());
            $this->ejecutarPaso('compras', fn() => $this->compras());
            $this->ejecutarPaso('ejemplares', fn() => $this->ejemplares());
            $this->ejecutarPaso('prestamos', fn() => $this->prestamos());
            $this->ejecutarPaso('reservaciones', fn() => $this->reservaciones());
            $this->ejecutarPaso('sanciones', fn() => $this->sanciones());
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            DB::commit();

            return response()->json([
                'success' => true,
                'mensaje' => 'Sincronizacion completada correctamente',
                'resumen' => $this->syncStats,
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Error en sincronizaciÃ³n', [
                'mensaje' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'linea' => $e->getLine()
            ], 500);
        }
    }

    public function sincronizarCirculacion()
    {
        DB::beginTransaction();

        try {
            $this->resetSyncStats();
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            DB::table('sanciones')->delete();
            DB::table('reservaciones')->delete();
            DB::table('prestamos')->delete();
            DB::table('ejemplares')->delete();

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->ejecutarPaso('ejemplares', fn() => $this->ejemplares());
            $this->ejecutarPaso('prestamos', fn() => $this->prestamos());
            $this->ejecutarPaso('reservaciones', fn() => $this->reservaciones());
            $this->ejecutarPaso('sanciones', fn() => $this->sanciones());

            DB::commit();

            return response()->json([
                'success' => true,
                'mensaje' => 'Circulacion resincronizada correctamente.',
                'resumen' => $this->syncStats,
            ]);
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            Log::error('Error en resincronizacion de circulacion', [
                'mensaje' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'linea' => $e->getLine(),
            ], 500);
        }
    }

    protected function ejecutarPaso($nombre, $callback)
    {
        try {
            $callback();
        } catch (\Exception $e) {
            throw new \Exception("âŒ Error en [$nombre]: " . $e->getMessage());
        }
    }

    /* =========================
     * TABLAS BASE
     * ========================= */

    protected function paises()
    {
        DB::connection('mysql2')->table('paises')
            ->chunkById(500, function ($rows) {

                $data = [];

                foreach ($rows as $r) {
                    $data[] = [
                        'id' => $r->id,
                        'nombre' => $r->nombre
                    ];
                }

                DB::table('paises')->upsert($data, ['id']);
            });
    }

    protected function editoriales()
    {
        DB::connection('mysql2')->table('editoriales')
            ->chunkById(500, function ($rows) {

                $data = [];

                foreach ($rows as $r) {
                    $data[] = [
                        'id' => $r->id,
                        'tipo_documento' => $r->identidad_tipo_id,
                        'nro_documento' => $r->nro_documento,
                        'nombre' => $r->nombre,
                        'responsable' => $r->responsable,
                        'telefono' => $r->telefono,
                        'correo' => $r->correo,
                        'direccion' => $r->direccion,
                        'web' => $r->web,
                        'pais' => $r->pais_id,
                        'estado' => $r->estado
                    ];
                }

                DB::table('editoriales')->upsert($data, ['id']);
            });
    }

    protected function autores()
    {
        DB::connection('mysql2')->table('autores')
            ->chunkById(500, function ($rows) {

                $data = [];

                foreach ($rows as $r) {
                    $data[] = [
                        'id' => $r->id,
                        'nombres' => $r->nombre,
                        'apellidos' => $r->apaterno . ' ' . $r->amaterno,
                        'pais' => $r->pais_id,
                        'estado' => $r->estado
                    ];
                }

                DB::table('autores')->upsert($data, ['id']);
            });
    }

    protected function materias()
    {
        DB::connection('mysql2')->table('materias')
            ->chunkById(500, function ($rows) {

                $data = [];

                foreach ($rows as $r) {
                    $data[] = [
                        'id' => $r->id,
                        'codigo' => $r->codigo,
                        'abreviatura' => $r->abreviatura,
                        'nombre' => $r->nombre
                    ];
                }

                DB::table('materias')->upsert($data, ['id']);
            });
    }

    protected function carreras()
    {
        DB::connection('mysql2')->table('carreras')
            ->chunkById(500, function ($rows) {

                $data = [];

                foreach ($rows as $r) {
                    $data[] = [
                        'id' => $r->id,
                        'nombre' => $r->nombre,
                        'codigo' => $r->codigo
                    ];
                }

                DB::table('carreras')->upsert($data, ['id']);
            });
    }

    protected function actividadCategorias()
    {
        $usuariosLocales = DB::table('users')->pluck('id')->flip()->all();
        $userSyncMap = $this->resolveUserSyncMap();

        DB::connection('mysql2')->table('actividad_categorias')
            ->orderBy('id')
            ->chunk(500, function ($rows) use ($usuariosLocales, $userSyncMap) {
                $data = [];
                $omitidos = 0;
                $motivos = [];

                foreach ($rows as $r) {
                    $usuarioId = $userSyncMap[$r->user_id] ?? $r->user_id;

                    if (
                        empty($r->id) ||
                        empty($r->nombre) ||
                        empty($usuarioId)
                    ) {
                        $omitidos++;
                        $motivos['datos_obligatorios_incompletos'] = ($motivos['datos_obligatorios_incompletos'] ?? 0) + 1;
                        continue;
                    }

                    if (! isset($usuariosLocales[$usuarioId])) {
                        $omitidos++;
                        $motivos['relaciones_foraneas_inexistentes'] = ($motivos['relaciones_foraneas_inexistentes'] ?? 0) + 1;
                        continue;
                    }

                    $abreviatura = trim((string) ($r->abreviatura ?? ''));
                    if ($abreviatura === '') {
                        $abreviatura = strtoupper(substr($this->normalizarClave($r->nombre), 0, 20));
                    }

                    $data[] = [
                        'id' => $r->id,
                        'abreviatura' => $abreviatura,
                        'nombre' => $r->nombre,
                        'descripcion' => $r->descripcion,
                        'user_id' => $usuarioId,
                        'estado' => (int) ($r->estado ?? 1),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if (! empty($data)) {
                    DB::table('actividad_categorias')->upsert(
                        $data,
                        ['id'],
                        ['abreviatura', 'nombre', 'descripcion', 'user_id', 'estado', 'updated_at']
                    );
                }

                $this->addSyncStat('actividad_categorias', count($data), $omitidos, $motivos);
            });
    }

    protected function actividades()
    {
        $usuariosLocales = DB::table('users')->pluck('id')->flip()->all();
        $userSyncMap = $this->resolveUserSyncMap();
        $categoriasLocales = DB::table('actividad_categorias')->pluck('id')->flip()->all();

        DB::connection('mysql2')->table('actividades')
            ->orderBy('id')
            ->chunk(500, function ($rows) use ($usuariosLocales, $userSyncMap, $categoriasLocales) {
                $data = [];
                $omitidos = 0;
                $motivos = [];

                foreach ($rows as $r) {
                    $usuarioId = $userSyncMap[$r->user_id] ?? $r->user_id;
                    $categoriaId = $r->actividad_categoria_id;

                    if (
                        empty($r->id) ||
                        empty($categoriaId) ||
                        empty($usuarioId) ||
                        empty($r->fecha_inicio) ||
                        empty($r->titulo)
                    ) {
                        $omitidos++;
                        $motivos['datos_obligatorios_incompletos'] = ($motivos['datos_obligatorios_incompletos'] ?? 0) + 1;
                        continue;
                    }

                    if (
                        ! isset($usuariosLocales[$usuarioId]) ||
                        ! isset($categoriasLocales[$categoriaId])
                    ) {
                        $omitidos++;
                        $motivos['relaciones_foraneas_inexistentes'] = ($motivos['relaciones_foraneas_inexistentes'] ?? 0) + 1;
                        continue;
                    }

                    $imagen = $r->imagen ? ltrim((string) $r->imagen, '/') : null;

                    $data[] = [
                        'id' => $r->id,
                        'actividad_categoria_id' => $categoriaId,
                        'fecha_inicio' => $r->fecha_inicio,
                        'fecha_fin' => $r->fecha_fin ?: $r->fecha_inicio,
                        'titulo' => $r->titulo,
                        'resumen' => $r->resumen ?? null,
                        'contenido' => $r->contenido,
                        'imagen' => $imagen,
                        'referencia' => $r->referencia,
                        'lugar' => $r->lugar ?? null,
                        'hora_inicio' => $r->hora_inicio ?? null,
                        'hora_fin' => $r->hora_fin ?? null,
                        'modalidad' => $r->modalidad ?? null,
                        'destacado' => (bool) ($r->destacado ?? false),
                        'user_id' => $usuarioId,
                        'estado' => (int) ($r->estado ?? 1),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if (! empty($data)) {
                    DB::table('actividades')->upsert(
                        $data,
                        ['id'],
                        [
                            'actividad_categoria_id',
                            'fecha_inicio',
                            'fecha_fin',
                            'titulo',
                            'resumen',
                            'contenido',
                            'imagen',
                            'referencia',
                            'lugar',
                            'hora_inicio',
                            'hora_fin',
                            'modalidad',
                            'destacado',
                            'user_id',
                            'estado',
                            'updated_at',
                        ]
                    );
                }

                $this->addSyncStat('actividades', count($data), $omitidos, $motivos);
            });
    }

    /* =========================
     * PERSONAS + USUARIOS
     * ========================= */

    protected function personas()
    {
        DB::connection('mysql2')->table('personas')
            ->chunkById(500, function ($rows) {

                $data = [];

                foreach ($rows as $r) {
                    $data[] = [
                        'id' => $r->id,
                        'dni' => $r->nro_documento,
                        'nombres' => $r->nombre,
                        'apellido_paterno' => $r->apaterno,
                        'apellido_materno' => $r->amaterno,
                        'email_personal' => $r->correo
                    ];
                }

                DB::table('personas')->upsert($data, ['id']);
            });
    }

    protected function usuarios()
    {
        DB::connection('mysql2')->table('users')
            ->chunkById(500, function ($rows) {

                foreach ($rows as $r) {
                    // Insertar o actualizar usuario en la nueva base
                    DB::table('users')->upsert([
                        'id'         => $r->id,
                        'name'       => $r->nombre,
                        'email'      => $r->email,
                        'password'   => $r->password,
                        'estado'     => $r->estado,
                        'persona_id' => $r->persona_id,
                    ], ['id']);

                    // Verificar si ya tiene rol asignado en la nueva base
                    $existeRol = DB::table('usuario_rol_bibliotecas')
                        ->where('user_id', $r->id)
                        ->exists();

                    if (!$existeRol) {
                        DB::table('usuario_rol_bibliotecas')->insert([
                            'user_id'       => $r->id,
                            'rol_id'        => !empty($r->rol_id) ? $r->rol_id : 5, // lector por defecto
                            'biblioteca_id' => $r->biblioteca_id ?? null,
                            'estado'        => $r->estado,
                            'created_at'    => now(),
                            'updated_at'    => now(),
                        ]);
                    }
                }
            });
    }


    /* ========================= 
     * LIBROS
     * ========================= */
    protected function libros()
    {
        DB::connection('mysql2')->table('registros')
            ->orderBy('id')
            ->chunk(300, function ($rows) {

                // ðŸ”¥ Obtener IDs Ãºnicos
                $editorialesIds = collect($rows)->pluck('editorial_id')->filter()->unique();
                $archivosIds    = collect($rows)->pluck('archivo_id')->filter()->unique();

                // ðŸ”¥ Cargar en memoria
                $editoriales = DB::table('editoriales')
                    ->whereIn('id', $editorialesIds)
                    ->pluck('id')
                    ->toArray();

                $archivos = DB::connection('mysql2')->table('archivos')
                    ->whereIn('id', $archivosIds)
                    ->get()
                    ->keyBy('id');

                $data = [];

                foreach ($rows as $r) {
                    try {
                        // ðŸ”¥ Buscar imagen asociada al registro
                        $imagen = DB::connection('mysql2')->table('registro_archivos')
                            ->where('registro_id', $r->id)
                            ->first();

                        $imagen_libro = $imagen
                            ? DB::connection('mysql2')->table('archivos')->find($imagen->archivo_id)
                            : null;

                        // ðŸ”¥ LIMPIAR editorial
                        $editorial_id = !empty($r->editorial_id) ? $r->editorial_id : null;

                        if (!is_null($editorial_id) && !in_array($editorial_id, $editoriales)) {
                            throw new \Exception("Editorial no existe ID: " . $editorial_id);
                        }

                        // ðŸ”¥ ARCHIVO SEGURO
                        $archivo = $archivos[$r->archivo_id] ?? null;

                        // ðŸ”¥ LIMPIAR RUTAS (eliminar "archivos/")
                        $ruta_archivo = $archivo ? str_replace('archivos/', '', $archivo->ruta) : null;
                        $ruta_imagen  = $imagen_libro ? str_replace('archivos/', '', $imagen_libro->ruta) : null;

                        $data[] = [
                            'id'              => $r->id,
                            'codigo_ant'      => $r->codigo ?? 'LIB-' . $r->id,
                            'idioma'          => $r->idioma_id ?? null,
                            'edicion'         => $r->edicion,
                            'anio_edicion'    => $r->edicion_year,
                            'paginas'         => $r->paginas,
                            'tipo_registro_id'=> $r->registro_tipo_id,
                            'palabras_clave'  => $r->palabras_clave,
                            'titulo'          => $r->titulo,
                            'isbn'            => $r->isbn,
                            'imagen'          => $ruta_imagen ? 'storage/libros/' . $ruta_imagen : null,
                            'archivo_indice'  => $ruta_archivo ? 'storage/indices/' . $ruta_archivo : null,
                            'editorial_id'    => $editorial_id,
                        ];

                    } catch (\Exception $e) {
                        throw new \Exception("Libro ID {$r->id}: " . $e->getMessage());
                    }
                }

                // ðŸ”¥ INSERT MASIVO
                if (!empty($data)) {
                    DB::table('libros')->upsert($data, ['id']);
                }
            });
    }

    protected function libro_relaciones()
    {
        // materias
        DB::connection('mysql2')->table('registro_materias')
            ->chunkById(500, function ($rows) {

                $data = [];

                foreach ($rows as $r) {
                    $data[] = [
                        'libro_id' => $r->registro_id,
                        'materia_id' => $r->materia_id
                    ];
                }

                DB::table('libro_materias')->upsert($data, ['libro_id','materia_id']);

            }, 'registro_id'); // ðŸ”¥ IMPORTANTE

        // autores
        DB::connection('mysql2')->table('registro_autores')
            ->chunkById(500, function ($rows) {

                $data = [];

                foreach ($rows as $r) {
                    $data[] = [
                        'libro_id' => $r->registro_id,
                        'autor_id' => $r->autor_id
                    ];
                }

                DB::table('autor_libros')->upsert($data, ['libro_id','autor_id']);

            }, 'registro_id'); // ðŸ”¥ IMPORTANTE
    }
    /* =========================
     * COMPRAS + EJEMPLARES
     * ========================= */

    protected function compras()
    {
        DB::connection('mysql2')->table('adquisiciones')
            ->chunkById(500, function ($rows) {

                $data = [];

                foreach ($rows as $r) {
                    $data[] = [
                        'id' => $r->id,
                        'codigo' => $r->codigo,
                        'fecha_compra' => $r->fecha
                    ];
                }

                DB::table('compras')->upsert($data, ['id']);
            });
    }

    protected function ejemplares()
    {
        DB::connection('mysql2')->table('ejemplares')
            ->chunkById(500, function ($rows) {

                $data = [];
                $omitidos = 0;
                $motivos = [];

                foreach ($rows as $r) {
                    if (empty($r->id) || empty($r->registro_id)) {
                        $omitidos++;
                        $motivos['sin_id_o_libro'] = ($motivos['sin_id_o_libro'] ?? 0) + 1;
                        continue;
                    }

                    $data[] = [
                        'id' => $r->id,
                        'codigo_interno' => $r->nro_ejemplar,
                        'codigo_ant' => $r->codigo,
                        'libro_id' => $r->registro_id,
                        'biblioteca_id' => $r->biblioteca_id,
                        'adquisicion' => $r->adquisicion_id,
                        'tipo' => 'eje.',
                        'estado' => $r->estado
                    ];
                }

                if (!empty($data)) {
                    DB::table('ejemplares')->upsert($data, ['id']);
                }

                $this->addSyncStat('ejemplares', count($data), $omitidos, $motivos);
            });
    }

    protected function prestamos()
    {
        $usuariosLocales = DB::table('users')->pluck('id')->flip()->all();
        $userSyncMap = $this->resolveUserSyncMap();
        $ejemplaresLocales = DB::table('ejemplares')->pluck('id')->flip()->all();

        // En mysql2 un prestamo puede tener varios prestamo_detalles.
        // En la tabla local guardamos un registro por cada detalle, usando el id del detalle
        // para conservar la relacion directa con ejemplar y sanciones.
        DB::connection('mysql2')->table('prestamo_detalles as pd')
            ->join('prestamos as p', 'p.id', '=', 'pd.prestamo_id')
            ->select([
                'pd.id',
                'pd.prestamo_id',
                'pd.ejemplar_id',
                'pd.user_id as detalle_user_id',
                'pd.observaciones as observaciones_devolucion',
                'pd.estado as estado_prestamo',
                'p.lector_id',
                'p.prestamo_lugar',
                'p.duracion',
                'p.fecha_prestamo',
                'p.fecha_limite',
                'p.fecha_devolucion',
                'p.observaciones as observaciones_prestamo',
                'p.estado',
                'p.user_id as prestamo_user_id',
            ])
            ->orderBy('pd.id')
            ->chunk(500, function ($rows) use ($usuariosLocales, $userSyncMap, $ejemplaresLocales) {
                $data = [];
                $omitidos = 0;
                $motivos = [];

                foreach ($rows as $r) {
                    $duracion = max((int) ($r->duracion ?? 0), 1);
                    $lectorId = $userSyncMap[$r->lector_id] ?? $r->lector_id;
                    $sourceUserId = $r->prestamo_user_id ?: $r->detalle_user_id ?: 1;
                    $usuarioRegistro = $userSyncMap[$sourceUserId] ?? $sourceUserId;
                    $fechaLimite = $r->fecha_limite;

                    if (empty($fechaLimite) && !empty($r->fecha_prestamo)) {
                        $fechaLimite = \Carbon\Carbon::parse($r->fecha_prestamo)
                            ->addDays($duracion)
                            ->setTime(20, 0, 0);
                    }

                    if (
                        empty($lectorId) ||
                        empty($r->ejemplar_id) ||
                        empty($usuarioRegistro) ||
                        empty($r->fecha_prestamo) ||
                        empty($fechaLimite)
                    ) {
                        $omitidos++;
                        $motivos['datos_obligatorios_incompletos'] = ($motivos['datos_obligatorios_incompletos'] ?? 0) + 1;
                        continue;
                    }

                    if (
                        !isset($usuariosLocales[$lectorId]) ||
                        !isset($usuariosLocales[$usuarioRegistro]) ||
                        !isset($ejemplaresLocales[$r->ejemplar_id])
                    ) {
                        $omitidos++;
                        $motivos['relaciones_foraneas_inexistentes'] = ($motivos['relaciones_foraneas_inexistentes'] ?? 0) + 1;
                        continue;
                    }

                    $data[] = [
                        'id' => $r->id,
                        'lector_id' => $lectorId,
                        'prestamo_lugar' => $r->prestamo_lugar,
                        'duracion' => $duracion,
                        'fecha_prestamo' => $r->fecha_prestamo,
                        'fecha_limite' => $fechaLimite,
                        'fecha_devolucion' => $r->fecha_devolucion,
                        'observaciones_prestamo' => $r->observaciones_prestamo,
                        'observaciones_devolucion' => $r->observaciones_devolucion,
                        'estado_prestamo' => (int) ($r->estado_prestamo ?? 0),
                        'estado' => (int) ($r->estado ?? 1),
                        'ejemplar_id' => $r->ejemplar_id,
                        'user_id' => $usuarioRegistro,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if (!empty($data)) {
                    DB::table('prestamos')->upsert(
                        $data,
                        ['id'],
                        [
                            'lector_id',
                            'prestamo_lugar',
                            'duracion',
                            'fecha_prestamo',
                            'fecha_limite',
                            'fecha_devolucion',
                            'observaciones_prestamo',
                            'observaciones_devolucion',
                            'estado_prestamo',
                            'estado',
                            'ejemplar_id',
                            'user_id',
                            'updated_at',
                        ]
                    );
                }

                $this->addSyncStat('prestamos', count($data), $omitidos, $motivos);
            });
    }

    protected function reservaciones()
    {
        $usuariosLocales = DB::table('users')->pluck('id')->flip()->all();
        $userSyncMap = $this->resolveUserSyncMap();
        $ejemplaresLocales = DB::table('ejemplares')->pluck('id')->flip()->all();

        DB::connection('mysql2')->table('reservaciones')
            ->orderBy('id')
            ->chunk(500, function ($rows) use ($usuariosLocales, $userSyncMap, $ejemplaresLocales) {
                $data = [];
                $omitidos = 0;
                $motivos = [];

                foreach ($rows as $r) {
                    $lectorId = $userSyncMap[$r->lector_id] ?? $r->lector_id;
                    if (
                        empty($r->id) ||
                        empty($r->ejemplar_id) ||
                        empty($lectorId)
                    ) {
                        $omitidos++;
                        $motivos['datos_obligatorios_incompletos'] = ($motivos['datos_obligatorios_incompletos'] ?? 0) + 1;
                        continue;
                    }

                    $fechaReservacion = $r->fecha_reservacion;
                    $duracion = (int) ($r->duracion ?? 1);
                    $fechaLimite = $r->fecha_limite;

                    if (empty($fechaLimite) && !empty($fechaReservacion)) {
                        $fechaLimite = \Carbon\Carbon::parse($fechaReservacion)
                            ->addDays(max($duracion, 1))
                            ->setTime(20, 0, 0);
                    }

                    if (empty($fechaLimite)) {
                        $omitidos++;
                        $motivos['sin_fecha_limite'] = ($motivos['sin_fecha_limite'] ?? 0) + 1;
                        continue;
                    }

                    $sourceBibliotecarioId = $r->user_id ?: null;
                    $bibliotecarioId = $sourceBibliotecarioId !== null
                        ? ($userSyncMap[$sourceBibliotecarioId] ?? $sourceBibliotecarioId)
                        : null;

                    if (
                        !isset($usuariosLocales[$lectorId]) ||
                        !isset($ejemplaresLocales[$r->ejemplar_id]) ||
                        ($bibliotecarioId !== null && !isset($usuariosLocales[$bibliotecarioId]))
                    ) {
                        $omitidos++;
                        $motivos['relaciones_foraneas_inexistentes'] = ($motivos['relaciones_foraneas_inexistentes'] ?? 0) + 1;
                        continue;
                    }

                    $data[] = [
                        'id' => $r->id,
                        'ejemplar_id' => $r->ejemplar_id,
                        'lector_id' => $lectorId,
                        'duracion' => $duracion,
                        'fecha_reservacion' => $fechaReservacion,
                        'fecha_limite' => $fechaLimite,
                        'prestamo' => $r->prestamo,
                        'bibliotecario_id' => $bibliotecarioId,
                        'estado' => (int) ($r->estado ?? 0),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if (!empty($data)) {
                    DB::table('reservaciones')->upsert(
                        $data,
                        ['id'],
                        [
                            'ejemplar_id',
                            'lector_id',
                            'duracion',
                            'fecha_reservacion',
                            'fecha_limite',
                            'prestamo',
                            'bibliotecario_id',
                            'estado',
                            'updated_at',
                        ]
                    );
                }

                $this->addSyncStat('reservaciones', count($data), $omitidos, $motivos);
            });
    }

    protected function sanciones()
    {
        $usuariosLocales = DB::table('users')->pluck('id')->flip()->all();
        $userSyncMap = $this->resolveUserSyncMap();
        $prestamosLocales = DB::table('prestamos')->pluck('id')->flip()->all();
        $reservacionesLocales = DB::table('reservaciones')->pluck('id')->flip()->all();

        DB::connection('mysql2')->table('sanciones')
            ->orderBy('id')
            ->chunk(500, function ($rows) use ($usuariosLocales, $userSyncMap, $prestamosLocales, $reservacionesLocales) {
                $data = [];
                $omitidos = 0;
                $motivos = [];

                foreach ($rows as $r) {
                    $lectorId = $userSyncMap[$r->lector_id] ?? $r->lector_id;

                    if (empty($r->id) || empty($lectorId)) {
                        $omitidos++;
                        $motivos['sin_id_o_lector'] = ($motivos['sin_id_o_lector'] ?? 0) + 1;
                        continue;
                    }

                    $prestamoId = $r->prestamo_detalle_id ?: null;
                    $reservacionId = $r->reservacion_id ?: null;
                    $sourceBibliotecarioId = $r->user_id ?: null;
                    $bibliotecarioId = $sourceBibliotecarioId !== null
                        ? ($userSyncMap[$sourceBibliotecarioId] ?? $sourceBibliotecarioId)
                        : null;

                    if (
                        !isset($usuariosLocales[$lectorId]) ||
                        ($bibliotecarioId !== null && !isset($usuariosLocales[$bibliotecarioId])) ||
                        ($prestamoId !== null && !isset($prestamosLocales[$prestamoId])) ||
                        ($reservacionId !== null && !isset($reservacionesLocales[$reservacionId]))
                    ) {
                        $omitidos++;
                        $motivos['relaciones_foraneas_inexistentes'] = ($motivos['relaciones_foraneas_inexistentes'] ?? 0) + 1;
                        continue;
                    }

                    $data[] = [
                        'id' => $r->id,
                        'user_id' => $lectorId,
                        'prestamo_id' => $prestamoId,
                        'reservacion_id' => $reservacionId,
                        'tipo' => $r->tipo,
                        'codigo_pago' => $r->codigo_pago,
                        'motivo' => $r->tipo ?: 'Sancion importada',
                        'fecha_inicio' => $r->fecha_inicio,
                        'fecha_fin' => $r->fecha_fin,
                        'duracion' => $r->duracion,
                        'observaciones' => $r->observaciones,
                        'detalles_termino' => $r->detalles_termino,
                        'bibliotecario_id' => $bibliotecarioId,
                        'estado' => (int) ($r->estado ?? 1),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if (!empty($data)) {
                    DB::table('sanciones')->upsert(
                        $data,
                        ['id'],
                        [
                            'user_id',
                            'prestamo_id',
                            'reservacion_id',
                            'tipo',
                            'codigo_pago',
                            'motivo',
                            'fecha_inicio',
                            'fecha_fin',
                            'duracion',
                            'observaciones',
                            'detalles_termino',
                            'bibliotecario_id',
                            'estado',
                            'updated_at',
                        ]
                    );
                }

                $this->addSyncStat('sanciones', count($data), $omitidos, $motivos);
            });
    }
}
