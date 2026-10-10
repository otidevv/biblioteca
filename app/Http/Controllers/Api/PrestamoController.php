<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Biblioteca;
use App\Models\Ejemplar;
use App\Models\Prestamo;
use App\Models\Sancion;
use App\Models\User;
use App\Models\Usuario_rol_biblioteca;
use App\Services\LectorLibreService;
use App\Services\SancionAutomaticaService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Auth;

class PrestamoController extends Controller
{
    public function listar(Request $request)
    {
        if (! auth()->check()) {
            return response()->json(['error' => 'Debes iniciar sesión'], 401);
        }

        $user = Auth::user();
        [$bibliotecasAsignadas, $accesoGlobal] = $this->resolverBibliotecasUsuario($user->id);

        $bibliotecaId = $request->integer('biblioteca_id') ?: null;
        $estado = in_array($request->input('estado'), ['curso', 'vencidos', 'devueltos', 'todos'], true)
            ? $request->input('estado')
            : 'curso';

        // Alcance: las bibliotecas asignadas al usuario y, si eligio una pestaña, solo esa.
        $alcance = Prestamo::query()
            ->when(! $accesoGlobal, function ($q) use ($bibliotecasAsignadas) {
                if ($bibliotecasAsignadas->isEmpty()) {
                    $q->whereRaw('1 = 0');
                    return;
                }

                $q->whereHas('ejemplar', fn ($sub) => $sub->whereIn('biblioteca_id', $bibliotecasAsignadas->all()));
            })
            ->when($bibliotecaId, fn ($q) => $q->whereHas('ejemplar', fn ($sub) => $sub->where('biblioteca_id', $bibliotecaId)));

        $query = (clone $alcance)->with(['ejemplar.libro', 'ejemplar.biblioteca', 'lector.persona']);

        match ($estado) {
            'curso' => $query->enCurso(),
            'vencidos' => $query->vencidos(),
            'devueltos' => $query->devueltos(),
            'todos' => $query,
        };

        // El ultimo prestamo registrado primero.
        $query->orderByDesc('prestamos.created_at')->orderByDesc('prestamos.id');

        $ahora = now();
        $vencidoSql = Prestamo::sqlFechaLimiteReal() . ' < ?';

        // Contadores de las pestañas de estado (para la biblioteca elegida).
        $porEstado = (clone $alcance)
            ->selectRaw(
                "SUM(prestamos.estado = 1) AS en_curso, SUM(prestamos.estado = 1 AND {$vencidoSql}) AS vencidos, "
                . 'SUM(prestamos.estado IN (2, 3)) AS devueltos, COUNT(*) AS todos',
                [$ahora]
            )
            ->toBase()
            ->first();

        // Contadores de las pestañas de biblioteca: prestamos en curso y vencidos de cada una.
        $porBiblioteca = Prestamo::query()
            ->join('ejemplares', 'ejemplares.id', '=', 'prestamos.ejemplar_id')
            ->enCurso()
            ->when(! $accesoGlobal, fn ($q) => $q->whereIn('ejemplares.biblioteca_id', $bibliotecasAsignadas->all()))
            ->groupBy('ejemplares.biblioteca_id')
            ->selectRaw("ejemplares.biblioteca_id, COUNT(*) AS en_curso, SUM({$vencidoSql}) AS vencidos", [$ahora])
            ->toBase()
            ->get()
            ->mapWithKeys(fn ($fila) => [$fila->biblioteca_id => [
                'en_curso' => (int) $fila->en_curso,
                'vencidos' => (int) $fila->vencidos,
            ]]);

        $resumen = [
            'estados' => [
                'en_curso' => (int) $porEstado->en_curso,
                'vencidos' => (int) $porEstado->vencidos,
                'devueltos' => (int) $porEstado->devueltos,
                'todos' => (int) $porEstado->todos,
            ],
            'bibliotecas' => $porBiblioteca,
        ];

        // 'late' fuera de plazo, 'soon' vence en menos de 24 h, 'ok' en plazo, 'done' devuelto.
        $vigencia = function (Prestamo $row) use ($ahora): string {
            if ((int) $row->estado !== 1) {
                return 'done';
            }

            $segundos = $ahora->diffInSeconds($row->fecha_limite_real, false);

            return $segundos <= 0 ? 'late' : ($segundos < 86400 ? 'soon' : 'ok');
        };

        return DataTables::eloquent($query)
            ->setRowClass(fn ($row) => 'loan-row loan-row--' . $vigencia($row))
            ->addColumn('fecha_limite_raw', function ($row) {
                return $row->fecha_limite_real->toDateTimeString();
            })
            ->addColumn('libro', function ($row) {
                $ejemplar = $row->ejemplar;
                $titulo = $ejemplar?->libro?->titulo ?? 'Libro no disponible';
                // Codigo antiguo/interno propio del ejemplar (no el del libro)
                $codigo = trim((string) ($ejemplar?->codigo_ant ?? ''));
                // N° de inventario propio del ejemplar: distingue cada copia fisica
                $inventario = ($ejemplar?->codigo_interno ?? '') !== '' ? 'Ej. N° ' . $ejemplar->codigo_interno : null;
                $biblioteca = $ejemplar?->biblioteca;
                $obsPrestamo = trim((string) $row->observaciones_prestamo);
                $obsDevolucion = trim((string) $row->observaciones_devolucion);

                return '<div class="loan-book">' .
                    '<span class="loan-book__icon" aria-hidden="true"><i class="bi bi-book-half"></i></span>' .
                    '<div class="loan-book__body">' .
                        '<div class="loan-book__title" title="' . e($titulo) . '">' . e($titulo) . '</div>' .
                        '<div class="loan-book__meta">' .
                            '<span class="loan-book__code">' . e($codigo !== '' ? $codigo : 'Sin código') . '</span>' .
                            ($inventario ? '<span>' . e($inventario) . '</span>' : '') .
                            ($biblioteca
                                ? '<span class="loan-book__lib" title="' . e($biblioteca->nombre) . '"><i class="bi bi-building"></i>' . e($biblioteca->nombre_corto) . '</span>'
                                : '') .
                        '</div>' .
                        ($obsPrestamo !== ''
                            ? '<div class="loan-book__obs" title="' . e($obsPrestamo) . '"><i class="bi bi-chat-left-text"></i>' . e($obsPrestamo) . '</div>'
                            : '') .
                        ($obsDevolucion !== ''
                            ? '<div class="loan-book__obs" title="' . e($obsDevolucion) . '"><i class="bi bi-arrow-return-left"></i>' . e($obsDevolucion) . '</div>'
                            : '') .
                    '</div>' .
                '</div>';
            })
            ->addColumn('lector', function ($row) {
                $user    = $row->lector;
                $persona = $user?->persona;

                if ($persona && ($persona->apellido_paterno || $persona->nombres)) {
                    $nombre = trim(trim($persona->apellido_paterno . ' ' . $persona->apellido_materno) . ', ' . $persona->nombres, ' ,');
                    $iniciales = $this->iniciales($persona->nombres . ' ' . $persona->apellido_paterno);
                } else {
                    $nombre = $user->name ?? 'Lector no disponible';
                    $iniciales = $this->iniciales($nombre);
                }

                $dni    = $persona->dni ?? '';
                $codigo = $persona->codigo_institucional ?? '';

                $etiquetas = '';
                if ($dni) {
                    $etiquetas .= '<span class="loan-reader__tag">DNI ' . e($dni) . '</span>';
                }
                if ($codigo) {
                    $etiquetas .= '<span class="loan-reader__tag loan-reader__tag--code">Cód. ' . e($codigo) . '</span>';
                }
                if ($persona && $persona->tieneCarnetDuplicado()) {
                    $desde = $persona->fecha_carnet_duplicado?->format('d/m/Y');
                    $etiquetas .= '<span class="loan-reader__tag loan-reader__tag--warn" title="Carnet duplicado' . ($desde ? ' desde ' . $desde : '') . '"><i class="bi bi-exclamation-triangle-fill"></i>Carnet duplicado</span>';
                }
                if (! $etiquetas && $user && $user->email) {
                    $etiquetas = '<span class="loan-reader__mail">' . e($user->email) . '</span>';
                }

                return '<div class="loan-reader">' .
                    '<span class="loan-reader__avatar" aria-hidden="true">' . e($iniciales) . '</span>' .
                    '<div class="loan-reader__body">' .
                        '<div class="loan-reader__name" title="' . e($nombre) . '">' . e($nombre) . '</div>' .
                        ($etiquetas ? '<div class="loan-reader__tags">' . $etiquetas . '</div>' : '') .
                    '</div>' .
                '</div>';
            })
            ->addColumn('fechas', function ($row) {
                // Los prestamos anteriores a guardar la hora quedan en 00:00: solo se muestra la fecha.
                $fp = $row->fecha_prestamo
                    ? $row->fecha_prestamo->format($row->fecha_prestamo->format('H:i:s') === '00:00:00' ? 'd/m/Y' : 'd/m/Y H:i')
                    : null;
                $fl = $row->fecha_limite_real->format('d/m/Y H:i');

                $lugar = (int) ($row->prestamo_lugar ?? 0) === Prestamo::A_CASA
                    ? '<span class="loan-tag"><i class="bi bi-house-door"></i>A casa</span>'
                    : '<span class="loan-tag"><i class="bi bi-door-open"></i>En sala</span>';
                $libre = $row->es_libre
                    ? '<span class="loan-tag loan-tag--libre" title="Préstamo libre por DNI"><i class="bi bi-person-vcard"></i>Libre</span>'
                    : '';

                return '<div class="loan-dates">' .
                    '<span class="loan-dates__main">' . ($fp ?: '-') . '</span>' .
                    '<span class="loan-dates__sub">Límite ' . $fl . '</span>' .
                    '<div class="loan-tags">' . $lugar . $libre . '</div>' .
                '</div>';
            })
            ->addColumn('estado', function ($row) use ($vigencia, $ahora) {
                $tipo = $vigencia($row);

                if ($tipo === 'done') {
                    $devuelto = $row->fecha_devolucion?->format('d/m/Y H:i');

                    return '<span class="loan-due loan-due--done"><i class="bi bi-check2-circle"></i>Cerrado</span>' .
                        ($devuelto ? '<small class="loan-due__sub">' . $devuelto . '</small>' : '');
                }

                if ($tipo === 'late') {
                    return '<span class="loan-due loan-due--late"><i class="bi bi-exclamation-octagon"></i>Fuera de plazo</span>' .
                        '<small class="loan-due__sub">Venció ' . e($row->fecha_limite_real->locale('es')->diffForHumans()) . '</small>';
                }

                $segundos = (int) $ahora->diffInSeconds($row->fecha_limite_real, false);

                return '<span class="loan-due loan-due--' . $tipo . '"><i class="bi bi-hourglass-split"></i><span class="countdown" data-seconds="' . $segundos . '"></span></span>' .
                    '<small class="loan-due__sub">' . ($tipo === 'soon' ? 'Vence pronto' : 'En plazo') . '</small>';
            })
            ->addColumn('estado_prestamo', function ($row) {
                [$clase, $texto] = match ((int) $row->estado_prestamo) {
                    0 => ['prestado', 'Prestado'],
                    1 => ['devuelto', 'Devuelto'],
                    2 => ['tardanza', 'Con tardanza'],
                    3 => ['deterioro', 'Deterioro'],
                    default => ['neutral', 'Sin estado'],
                };

                return '<span class="loan-state loan-state--' . $clase . '">' . $texto . '</span>';
            })
            ->addColumn('acciones', function ($row) {
                return (int) $row->estado === 1
                    ? '<button type="button" class="loan-return-btn devolverPrestamo" data-id="' . $row->id . '"><i class="bi bi-box-arrow-in-down-left"></i><span>Devolver</span></button>'
                    : '<span class="loan-return-done" title="Préstamo cerrado"><i class="bi bi-check2-all"></i></span>';
            })
            ->filterColumn('lector', function ($query, $keyword) {
                $query->whereHas('lector', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                      ->orWhere('email', 'like', "%{$keyword}%")
                      ->orWhereHas('persona', function ($p) use ($keyword) {
                          $p->where('dni', 'like', "%{$keyword}%")
                            ->orWhere('codigo_institucional', 'like', "%{$keyword}%")
                            ->orWhere('nombres', 'like', "%{$keyword}%")
                            ->orWhere('apellido_paterno', 'like', "%{$keyword}%")
                            ->orWhere('apellido_materno', 'like', "%{$keyword}%");
                      });
                });
            })
            // La columna muestra libro y ejemplar: se busca por titulo, ISBN o codigos del ejemplar.
            ->filterColumn('libro', function ($query, $keyword) {
                $query->whereHas('ejemplar', function ($q) use ($keyword) {
                    $q->where('codigo_ant', 'like', "%{$keyword}%")
                      ->orWhere('codigo_dewey', 'like', "%{$keyword}%")
                      ->orWhere('codigo_interno', 'like', "%{$keyword}%")
                      ->orWhereHas('libro', function ($l) use ($keyword) {
                          $l->where('titulo', 'like', "%{$keyword}%")
                            ->orWhere('isbn', 'like', "%{$keyword}%");
                      });
                });
            })
            ->rawColumns(['libro', 'lector', 'fechas', 'estado', 'estado_prestamo', 'acciones'])
            ->with('resumen', $resumen)
            ->toJson();
    }

    // Dos letras para el avatar del lector.
    private function iniciales(string $texto): string
    {
        return collect(preg_split('/[\s,]+/u', trim($texto), -1, PREG_SPLIT_NO_EMPTY))
            ->take(2)
            ->map(fn ($parte) => mb_strtoupper(mb_substr($parte, 0, 1)))
            ->implode('') ?: '?';
    }

    public function nuevoPrestamo(Request $request)
    {
        if (! auth()->check()) {
            return response()->json(['error' => 'Debes iniciar sesión'], 401);
        }

        $request->validate([
            'libro_id' => 'required',
            'lector_id' => 'required',
            'prestamo_lugar' => 'required',
            'duracion' => 'required|integer|min:1',
        ]);

        $activos = Prestamo::where('lector_id', $request->lector_id)
            ->where('estado', 'prestado')
            ->count();

        if ($activos >= 3) {
            return response()->json([
                'error' => 'Ya tienes 3 préstamos activos',
            ]);
        }

        $ejemplar = Ejemplar::where('libro_id', $request->libro_id)
            ->where('estado', '1')
            ->first();

        if (! $ejemplar) {
            return response()->json([
                'error' => 'No hay ejemplares disponibles',
            ]);
        }

        $fechaPrestamo = now();
        $fechaLimite = now()->addDays($request->duracion);

        Prestamo::create([
            'lector_id' => $request->lector_id,
            'user_id' => auth()->id(),
            'prestamo_lugar' => $request->prestamo_lugar,
            'duracion' => $request->duracion,
            'fecha_prestamo' => $fechaPrestamo,
            'fecha_limite' => $fechaLimite,
            'observaciones' => null,
            'estado' => 'prestado',
        ]);

        $ejemplar->update([
            'estado' => '0',
        ]);

        return response()->json([
            'ok' => 'Prestamo registrado correctamente',
        ]);
    }

    public function devolver(Request $request, $id)
    {
        if (! auth()->check()) {
            return response()->json(['error' => 'Debes iniciar sesión'], 401);
        }

        $user = Auth::user();
        [$bibliotecasAsignadas, $accesoGlobal] = $this->resolverBibliotecasUsuario($user->id);

        $prestamo = Prestamo::with('ejemplar')->find($id);

        if (! $prestamo) {
            return response()->json(['error' => 'No se encontró el préstamo']);
        }

        if (! $accesoGlobal && ! $bibliotecasAsignadas->contains((int) optional($prestamo->ejemplar)->biblioteca_id)) {
            return response()->json(['error' => 'No puedes devolver prestamos de otra biblioteca'], 403);
        }

        $now = Carbon::now();
        $fechaLimite = $prestamo->fecha_limite_real;

        $estadoLibro = (int) ($request->estado_libro ?? 1);
        $estadoPrestamo = $estadoLibro !== 1
            ? 3
            : ($now->greaterThan($fechaLimite) ? 2 : 1);

        $prestamo->update([
            'fecha_devolucion' => $now,
            'observaciones_devolucion' => $request->observaciones,
            'estado' => 2,
            'estado_prestamo' => $estadoPrestamo,
            'estado_libro' => $estadoLibro,
        ]);

        $prestamo->ejemplar->update([
            'estado' => 1,
        ]);

        app(SancionAutomaticaService::class)->registrarPorPrestamo($prestamo->fresh());

        return response()->json([
            'ok' => 'Devolucion registrada correctamente',
        ]);
    }

    public function previewSancion(Request $request, $id)
    {
        if (! auth()->check()) {
            return response()->json(['error' => 'Debes iniciar sesión'], 401);
        }

        $prestamo = Prestamo::find($id);

        if (! $prestamo) {
            return response()->json(['error' => 'No se encontró el préstamo'], 404);
        }

        $estadoLibro = (int) $request->input('estado_libro', 1);
        $diasRetraso = $request->filled('dias_retraso') ? max(0, (int) $request->input('dias_retraso')) : null;

        return response()->json(
            app(SancionAutomaticaService::class)->previsualizarPorPrestamo($prestamo, $estadoLibro, $diasRetraso)
        );
    }

    public function biblioteacasAccesibles()
    {
        if (! auth()->check()) {
            return response()->json(['error' => 'Debes iniciar sesión'], 401);
        }

        [$bibliotecasAsignadas, $accesoGlobal] = $this->resolverBibliotecasUsuario(auth()->id());

        $query = Biblioteca::orderBy('nombre');

        if (! $accesoGlobal) {
            if ($bibliotecasAsignadas->isEmpty()) {
                return response()->json([]);
            }
            $query->whereIn('id', $bibliotecasAsignadas->all());
        }

        return response()->json($query->get(['id', 'nombre']));
    }

    public function buscarEjemplaresDisponibles(Request $request)
    {
        if (! auth()->check()) {
            return response()->json(['error' => 'Debes iniciar sesión'], 401);
        }

        $q = trim((string) $request->input('q', ''));
        $user = auth()->user();
        [$bibliotecasAsignadas, $accesoGlobal] = $this->resolverBibliotecasUsuario($user->id);

        $bibFiltro = $request->input('biblioteca_id');

        $query = Ejemplar::with(['libro', 'biblioteca'])
            ->where('estado', 1)
            ->when(! $accesoGlobal, function ($q) use ($bibliotecasAsignadas) {
                if ($bibliotecasAsignadas->isEmpty()) {
                    $q->whereRaw('1 = 0');
                    return;
                }
                $q->whereIn('biblioteca_id', $bibliotecasAsignadas->all());
            })
            ->when($bibFiltro, fn ($q) => $q->where('biblioteca_id', $bibFiltro))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->whereHas('libro', fn ($l) => $l->where('titulo', 'like', "%{$q}%"))
                        ->orWhere('codigo_ant', 'like', "%{$q}%")
                        ->orWhere('codigo_dewey', 'like', "%{$q}%");
                });
            })
            ->limit(20)
            ->get();

        return response()->json($query->map(function ($e) {
            return [
                'id'             => $e->id,
                'libro'          => $e->libro->titulo ?? '—',
                // Datos propios del ejemplar (no del libro): identifican cada copia
                'codigo_interno' => $e->codigo_interno,
                'codigo_ant'     => $e->codigo_ant,
                'codigo_dewey'   => $e->codigo_dewey,
                'biblioteca'     => $e->biblioteca->nombre ?? '—',
            ];
        }));
    }

    public function prestamoDirecto(Request $request)
    {
        if (! auth()->check()) {
            return response()->json(['error' => 'Debes iniciar sesión'], 401);
        }

        $request->validate([
            'lector_id'      => 'required|exists:users,id',
            'ejemplar_id'    => 'required|exists:ejemplares,id',
            'prestamo_lugar' => 'required|integer|in:0,1',
            'dias'           => 'exclude_if:prestamo_lugar,0|required|integer|min:1',
            'observaciones'  => 'nullable|string',
        ]);

        // En sala: vence hoy al cierre, por eso no se registra despues de esa hora.
        $enSala = (int) $request->prestamo_lugar === Prestamo::EN_SALA;

        if ($enSala && ($cerrado = $this->salaCerrada())) {
            return $cerrado;
        }

        // Solo los lectores registrados llevan libros a casa (los de prestamo libre, no).
        if (! $enSala && ! User::whereKey($request->lector_id)->whereHas('roles', fn ($q) => $q->where('roles.id', 5))->exists()) {
            return response()->json(['error' => 'Solo los lectores registrados pueden llevar libros a casa.'], 422);
        }

        $dias = $enSala ? 0 : (int) $request->dias;

        $resultado = $this->registrarPrestamo(
            $request,
            fn () => (int) $request->lector_id,
            (int) $request->prestamo_lugar,
            $dias
        );

        return response()->json($resultado['payload'], $resultado['status']);
    }

    public function consultarEstudianteLibre(Request $request, LectorLibreService $servicio)
    {
        $request->validate(['dni' => ['required', 'string', 'regex:/^[0-9]{8}$/']], [
            'dni.regex' => 'El DNI debe tener 8 dígitos.',
        ]);

        $consulta = $servicio->consultar($request->dni);

        if (! $consulta['ok']) {
            return response()->json(['error' => $consulta['message']], $consulta['status']);
        }

        $info = $consulta['info'];

        return response()->json([
            'dni' => $request->dni,
            'nombre' => trim($info['apellido_paterno'] . ' ' . $info['apellido_materno']) . ', ' . $info['nombres'],
            'codigo' => $info['codigo'],
            'carrera' => $info['carrera'],
            'carrera_id' => $info['carrera_id'],
            'email' => $info['email'],
            'registrado' => $consulta['registrado'],
            'es_lector' => $consulta['es_lector'],
            'carnet' => $consulta['carnet'] ?? null,
            'carnet_fecha' => $consulta['carnet_fecha'] ?? null,
        ]);
    }

    // Prestamo libre: estudiante identificado por DNI, solo en sala.
    public function prestamoLibre(Request $request, LectorLibreService $servicio)
    {
        $request->validate([
            'dni'           => ['required', 'string', 'regex:/^[0-9]{8}$/'],
            'nombre'        => 'nullable|string|max:255',
            'carrera_id'    => 'nullable|integer|exists:carreras,id',
            'ejemplar_id'   => 'required|exists:ejemplares,id',
            'observaciones' => 'nullable|string',
        ], [
            'dni.regex' => 'El DNI debe tener 8 dígitos.',
        ]);

        if ($cerrado = $this->salaCerrada()) {
            return $cerrado;
        }

        // La API se consulta antes de abrir la transaccion.
        $consulta = $servicio->consultar($request->dni);

        if ($consulta['ok']) {
            $info = $consulta['info'];
            // Si la carrera del sistema academico no esta en el catalogo, vale la elegida a mano.
            $info['carrera_id'] = $info['carrera_id'] ?: ($request->integer('carrera_id') ?: null);
        } elseif (filled($request->nombre) && $request->filled('carrera_id')) {
            // Sin datos en el sistema academico: se usan los ingresados por el bibliotecario.
            $info = $servicio->infoManual($request->dni, $request->nombre, $request->integer('carrera_id'));
        } else {
            return response()->json(['error' => $consulta['message'] . ' Ingresa el nombre completo y la carrera del estudiante.'], 422);
        }

        $resultado = $this->registrarPrestamo(
            $request,
            fn () => $servicio->usuarioParaPrestamo($request->dni, $info)->id,
            Prestamo::EN_SALA,
            0,
            true
        );

        return response()->json($resultado['payload'], $resultado['status']);
    }

    private function salaCerrada()
    {
        if (now()->lt(Prestamo::cierreDeHoy())) {
            return null;
        }

        return response()->json([
            'error' => 'Los préstamos en sala solo se registran hasta las ' . Prestamo::cierreDeHoy()->format('H:i') . '.',
        ], 422);
    }

    /**
     * Valida el ejemplar y la sancion del lector y registra el prestamo.
     * El lector se resuelve despues de validar el ejemplar, para no crear registros si el prestamo no procede.
     */
    private function registrarPrestamo(Request $request, callable $resolverLector, int $lugar, int $dias, bool $libre = false): array
    {
        return DB::transaction(function () use ($request, $resolverLector, $lugar, $dias, $libre) {
            $user = auth()->user();
            [$bibliotecasAsignadas, $accesoGlobal] = $this->resolverBibliotecasUsuario($user->id);

            $ejemplar = Ejemplar::lockForUpdate()->find($request->ejemplar_id);

            if (! $ejemplar || (int) $ejemplar->estado !== 1) {
                return ['status' => 422, 'payload' => ['error' => 'El ejemplar ya no está disponible']];
            }

            if (! $accesoGlobal && ! $bibliotecasAsignadas->contains((int) $ejemplar->biblioteca_id)) {
                return ['status' => 403, 'payload' => ['error' => 'No tienes acceso a este ejemplar']];
            }

            $lectorId = $resolverLector();

            $tieneSancion = Sancion::where('user_id', $lectorId)
                ->vigenteEnBiblioteca($ejemplar->biblioteca_id)
                ->exists();

            if ($tieneSancion) {
                return ['status' => 422, 'payload' => ['error' => 'El lector tiene una sanción vigente en esta biblioteca y no puede recibir préstamos']];
            }

            $prestamo = new Prestamo;
            $prestamo->lector_id      = $lectorId;
            $prestamo->prestamo_lugar = $lugar;
            $prestamo->es_libre       = $libre;
            $prestamo->duracion       = $dias;
            $prestamo->fecha_prestamo = now();
            $prestamo->fecha_limite   = now()->addDays($dias);
            $prestamo->observaciones_prestamo = $request->observaciones;
            $prestamo->ejemplar_id    = $ejemplar->id;
            $prestamo->estado         = 1;
            $prestamo->estado_prestamo = 0;
            $prestamo->user_id        = $user->id;
            $prestamo->save();

            $ejemplar->estado = 0;
            $ejemplar->save();

            return ['status' => 200, 'payload' => ['success' => 'Préstamo registrado correctamente']];
        });
    }

    private function resolverBibliotecasUsuario(int $userId): array
    {
        $asignaciones = Usuario_rol_biblioteca::query()
            ->where('user_id', $userId)
            ->get(['biblioteca_id', 'estado']);

        $bibliotecasAsignadas = $asignaciones
            ->pluck('biblioteca_id')
            ->filter(fn ($id) => ! is_null($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $accesoGlobal = $bibliotecasAsignadas->isEmpty()
            && $asignaciones->contains(fn ($asignacion) => is_null($asignacion->biblioteca_id) && (int) $asignacion->estado === 1);

        return [$bibliotecasAsignadas, $accesoGlobal];
    }
}
