<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ejemplar;
use App\Models\Libro;
use App\Models\MovimientoEjemplar;
use App\Models\User;
use App\Services\ReporteInventarioFisicoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;
use Yajra\DataTables\Facades\DataTables;

class EjemplarController extends Controller
{
    public function __construct(
        protected ReporteInventarioFisicoService $bibliotecaService
    ) {
    }

    public function listar(Request $request)
    {
        $contexto = $this->resolverContextoUsuario($request->user());

        $query = Ejemplar::with([
                'compra_detalle.compra',
                'biblioteca',
                'trasladoOrigenBiblioteca',
                'trasladoDestinoBiblioteca',
            ])
            ->where('libro_id', $request->id);

        if ($request->has('biblioteca_id')) {
            if ($request->biblioteca_id === null || $request->biblioteca_id === '') {
                $query->whereNull('biblioteca_id');
            } elseif ($request->biblioteca_id != -1) {
                $bibliotecaId = (int) $request->biblioteca_id;

                $query->where(function ($scope) use ($bibliotecaId) {
                    $scope->where('biblioteca_id', $bibliotecaId)
                        ->orWhere(function ($pendingScope) use ($bibliotecaId) {
                            $pendingScope
                                ->where('estado_traslado', Ejemplar::TRASLADO_PENDIENTE)
                                ->where('traslado_destino_biblioteca_id', $bibliotecaId);
                        });
                });
            }
        }

        if ($request->has('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];

            $query->where(function ($q) use ($search) {
                $q->where('codigo_interno', 'like', "%{$search}%")
                    ->orWhere('codigo_dewey', 'like', "%{$search}%")
                    ->orWhere('codigo_ant', 'like', "%{$search}%")
                    ->orWhere('siaf', 'like', "%{$search}%")
                    ->orWhereHas('biblioteca', function ($bibliotecaQuery) use ($search) {
                        $bibliotecaQuery->where('nombre', 'like', "%{$search}%");
                    })
                    ->orWhereHas('trasladoDestinoBiblioteca', function ($bibliotecaQuery) use ($search) {
                        $bibliotecaQuery->where('nombre', 'like', "%{$search}%");
                    });
            });
        }

        return DataTables::of($query)
            ->addColumn('estado_value', function ($row) {
                return (int) $row->estado;
            })
            ->addColumn('can_move', function ($row) use ($contexto) {
                return $this->canMoveEjemplar($row, $contexto);
            })
            ->addColumn('can_resolve_transfer', function ($row) use ($contexto) {
                return $this->canResolveTransfer($row, $contexto);
            })
            ->addColumn('biblioteca', function ($row) {
                $actual = $row->biblioteca
                    ? '<span class="exemplars-table__library">' . e($row->biblioteca->nombre) . '</span>'
                    : '<span class="exemplars-table__library exemplars-table__library--empty">Sin biblioteca</span>';

                if ((int) $row->estado_traslado === Ejemplar::TRASLADO_PENDIENTE && $row->trasladoDestinoBiblioteca) {
                    return $actual
                        . '<div class="mt-1"><span class="exemplars-table__library exemplars-table__library--empty">Pendiente en '
                        . e($row->trasladoDestinoBiblioteca->nombre)
                        . '</span></div>';
                }

                return $actual;
            })
            ->editColumn('estado', function ($row) {
                $label = match ((int) $row->estado) {
                    Ejemplar::ESTADO_PRESTADO => 'Prestado',
                    Ejemplar::ESTADO_DISPONIBLE => 'Disponible',
                    Ejemplar::ESTADO_RESERVADO => 'Reservado',
                    Ejemplar::ESTADO_TRASLADO_PENDIENTE => 'Pendiente de aceptacion',
                    default => 'Sin definir',
                };

                $modifier = match ((int) $row->estado) {
                    Ejemplar::ESTADO_PRESTADO => 'warning',
                    Ejemplar::ESTADO_DISPONIBLE => 'success',
                    Ejemplar::ESTADO_RESERVADO => 'danger',
                    Ejemplar::ESTADO_TRASLADO_PENDIENTE => 'neutral',
                    default => 'neutral',
                };

                return '<span class="exemplars-status exemplars-status--' . $modifier . '">' . $label . '</span>';
            })
            ->addColumn('acciones', function ($row) use ($contexto) {
                if ($this->canResolveTransfer($row, $contexto)) {
                    return '<div class="d-flex gap-1">'
                        . '<button type="button" onclick="resolverTraslado(' . $row->id . ', \'aceptar\')" class="btn btn-sm btn-success">Aceptar</button>'
                        . '<button type="button" onclick="resolverTraslado(' . $row->id . ', \'rechazar\')" class="btn btn-sm btn-outline-danger">Rechazar</button>'
                        . '</div>';
                }

                if (! $this->canEditEjemplar($row, $contexto)) {
                    return '<span class="text-muted small">Sin acciones</span>';
                }

                return '<button type="button" onclick="actualizarEjemplar(' . $row->id . ')" class="btn btn-sm btn-primary editarEjemplar">Actualizar</button>';
            })
            ->rawColumns(['biblioteca', 'estado', 'acciones'])
            ->make(true);
    }

    public function listarMovimientos(Request $request)
    {
        $query = MovimientoEjemplar::query()
            ->with([
                'ejemplar',
                'bibliotecaOrigen',
                'bibliotecaDestino',
                'solicitadoPor',
                'resueltoPor',
            ])
            ->where('libro_id', $request->id)
            ->latest('id');

        return DataTables::of($query)
            ->addColumn('codigo', function (MovimientoEjemplar $movimiento) {
                $ejemplar = $movimiento->ejemplar;

                if (! $ejemplar) {
                    return '<span class="text-muted">Ejemplar no disponible</span>';
                }

                $codigo = trim(implode(' ', array_filter([
                    $ejemplar->codigo_dewey ?: $ejemplar->codigo_ant,
                    $ejemplar->tipo ? $ejemplar->tipo . ($ejemplar->codigo_interno ?? '') : null,
                ])));

                return '<span class="exemplars-history__code">' . e($codigo ?: 'Sin codigo') . '</span>';
            })
            ->addColumn('origen', function (MovimientoEjemplar $movimiento) {
                return e($movimiento->bibliotecaOrigen?->nombre ?? 'Sin biblioteca');
            })
            ->addColumn('destino', function (MovimientoEjemplar $movimiento) {
                return e($movimiento->bibliotecaDestino?->nombre ?? 'Sin biblioteca');
            })
            ->addColumn('solicitado_por', function (MovimientoEjemplar $movimiento) {
                $usuario = $movimiento->solicitadoPor?->name ?? 'Usuario no disponible';
                $fecha = optional($movimiento->solicitado_en)->format('d/m/Y H:i') ?? '-';

                return '<div class="exemplars-history__user"><strong>' . e($usuario) . '</strong><small>' . e($fecha) . '</small></div>';
            })
            ->addColumn('resuelto_por', function (MovimientoEjemplar $movimiento) {
                if (! $movimiento->resueltoPor) {
                    return '<span class="text-muted">Pendiente</span>';
                }

                $usuario = $movimiento->resueltoPor->name;
                $fecha = optional($movimiento->resuelto_en)->format('d/m/Y H:i') ?? '-';

                return '<div class="exemplars-history__user"><strong>' . e($usuario) . '</strong><small>' . e($fecha) . '</small></div>';
            })
            ->addColumn('estado_label', function (MovimientoEjemplar $movimiento) {
                $label = match ($movimiento->estado) {
                    MovimientoEjemplar::ESTADO_ACEPTADO => 'Aceptado',
                    MovimientoEjemplar::ESTADO_RECHAZADO => 'Rechazado',
                    default => 'Pendiente',
                };

                $modifier = match ($movimiento->estado) {
                    MovimientoEjemplar::ESTADO_ACEPTADO => 'success',
                    MovimientoEjemplar::ESTADO_RECHAZADO => 'danger',
                    default => 'neutral',
                };

                return '<span class="exemplars-status exemplars-status--' . $modifier . '">' . $label . '</span>';
            })
            ->rawColumns(['codigo', 'solicitado_por', 'resuelto_por', 'estado_label'])
            ->make(true);
    }

    public function listarTrasladosPendientes(Request $request)
    {
        $contexto = $this->resolverContextoUsuario($request->user());

        $query = $this->baseTrasladosPendientesQuery($contexto);

        return DataTables::of($query)
            ->addColumn('seleccion', function (MovimientoEjemplar $movimiento) use ($contexto) {
                $disabled = $this->canAcceptMovement($movimiento, $contexto) ? '' : 'disabled';
                return '<input type="checkbox" class="check-traslado-pendiente" value="' . $movimiento->id . '" ' . $disabled . '>';
            })
            ->addColumn('libro', function (MovimientoEjemplar $movimiento) {
                return e($movimiento->libro?->titulo ?? 'Libro no disponible');
            })
            ->addColumn('ejemplar_codigo', function (MovimientoEjemplar $movimiento) {
                return $this->renderMovimientoCodigo($movimiento);
            })
            ->addColumn('origen', function (MovimientoEjemplar $movimiento) {
                return e($movimiento->bibliotecaOrigen?->nombre ?? 'Sin biblioteca');
            })
            ->addColumn('solicitado_por', function (MovimientoEjemplar $movimiento) {
                return $this->renderMovimientoUsuario($movimiento->solicitadoPor?->name, $movimiento->solicitado_en);
            })
            ->addColumn('acciones', function (MovimientoEjemplar $movimiento) use ($contexto) {
                if (! $this->canAcceptMovement($movimiento, $contexto)) {
                    return '<span class="text-muted small">Sin acciones</span>';
                }

                return '<div class="d-flex gap-1">'
                    . '<button type="button" class="btn btn-sm btn-success" onclick="procesarTraslados(\'aceptar\', [' . $movimiento->id . '])">Aceptar</button>'
                    . '<button type="button" class="btn btn-sm btn-outline-danger" onclick="procesarTraslados(\'rechazar\', [' . $movimiento->id . '])">Rechazar</button>'
                    . '</div>';
            })
            ->rawColumns(['seleccion', 'ejemplar_codigo', 'solicitado_por', 'acciones'])
            ->make(true);
    }

    public function listarTrasladosEnviados(Request $request)
    {
        $contexto = $this->resolverContextoUsuario($request->user());

        $query = $this->baseTrasladosEnviadosQuery($contexto);

        return DataTables::of($query)
            ->addColumn('seleccion', function (MovimientoEjemplar $movimiento) use ($contexto) {
                $disabled = $this->canCancelMovement($movimiento, $contexto) ? '' : 'disabled';
                return '<input type="checkbox" class="check-traslado-enviado" value="' . $movimiento->id . '" ' . $disabled . '>';
            })
            ->addColumn('libro', function (MovimientoEjemplar $movimiento) {
                return e($movimiento->libro?->titulo ?? 'Libro no disponible');
            })
            ->addColumn('ejemplar_codigo', function (MovimientoEjemplar $movimiento) {
                return $this->renderMovimientoCodigo($movimiento);
            })
            ->addColumn('destino', function (MovimientoEjemplar $movimiento) {
                return e($movimiento->bibliotecaDestino?->nombre ?? 'Sin biblioteca');
            })
            ->addColumn('solicitado_por', function (MovimientoEjemplar $movimiento) {
                return $this->renderMovimientoUsuario($movimiento->solicitadoPor?->name, $movimiento->solicitado_en);
            })
            ->addColumn('acciones', function (MovimientoEjemplar $movimiento) use ($contexto) {
                if (! $this->canCancelMovement($movimiento, $contexto)) {
                    return '<span class="text-muted small">Sin acciones</span>';
                }

                return '<button type="button" class="btn btn-sm btn-outline-danger" onclick="procesarTraslados(\'cancelar\', [' . $movimiento->id . '])">Cancelar</button>';
            })
            ->rawColumns(['seleccion', 'ejemplar_codigo', 'solicitado_por', 'acciones'])
            ->make(true);
    }

    public function procesarAccionTraslados(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'movimiento_ids' => 'required|array|min:1',
            'movimiento_ids.*' => 'integer|exists:movimiento_ejemplares,id',
            'accion' => 'required|in:aceptar,rechazar,cancelar',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $contexto = $this->resolverContextoUsuario($request->user());
            $movimientos = MovimientoEjemplar::query()
                ->with('ejemplar')
                ->whereIn('id', $request->movimiento_ids)
                ->lockForUpdate()
                ->get();

            if ($movimientos->count() !== count($request->movimiento_ids)) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Uno o mas movimientos ya no existen.',
                ], 422);
            }

            foreach ($movimientos as $movimiento) {
                $this->aplicarAccionMovimiento($movimiento, $request->accion, $request->user(), $contexto);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $this->buildTrasladoActionMessage($request->accion, $movimientos->count()),
            ]);
        } catch (\RuntimeException $exception) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        } catch (\Throwable $exception) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'No se pudo procesar la accion sobre los traslados seleccionados.',
            ], 500);
        }
    }

    public function guardar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cantidad' => 'required|integer|min:1',
            'libro_id' => 'required|exists:libros,id',
            'biblioteca_id' => 'required|exists:bibliotecas,id',
            'siaf' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        if (! $this->usuarioPuedeAsignarBiblioteca($request->user(), (int) $request->biblioteca_id)) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes registrar ejemplares en una biblioteca fuera de tu asignacion.',
            ], 403);
        }

        DB::beginTransaction();

        try {
            $libro = Libro::findOrFail($request->libro_id);
            $ultimoCodigo = Ejemplar::where('libro_id', $request->libro_id)->max('codigo_interno') ?: 0;

            for ($i = 1; $i <= $request->cantidad; $i++) {
                Ejemplar::create([
                    'libro_id' => $request->libro_id,
                    'biblioteca_id' => $request->biblioteca_id,
                    'codigo_interno' => $ultimoCodigo + $i,
                    'tipo' => 'ej.',
                    'siaf' => $request->siaf,
                    'codigo_dewey' => $libro->codigo_dewey . $libro->codigo,
                    'estado' => Ejemplar::ESTADO_DISPONIBLE,
                    'estado_traslado' => Ejemplar::TRASLADO_NINGUNO,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ejemplares agregados correctamente',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function actualizar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|min:1|exists:ejemplares,id',
            'biblioteca_id' => 'required|exists:bibliotecas,id',
            'siaf' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $ejemplar = Ejemplar::findOrFail($request->id);
            $contexto = $this->resolverContextoUsuario($request->user());

            if (! $this->canEditEjemplar($ejemplar, $contexto)) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'No puedes actualizar un ejemplar fuera de tu biblioteca asignada o con traslado pendiente.',
                ], 403);
            }

            if (! $this->usuarioPuedeAsignarBiblioteca($request->user(), (int) $request->biblioteca_id)) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'No puedes asignar ese ejemplar a una biblioteca fuera de tu contexto.',
                ], 403);
            }

            $ejemplar->biblioteca_id = $request->biblioteca_id;
            $ejemplar->siaf = $request->siaf;
            $ejemplar->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ejemplar actualizado correctamente',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'error' => 'Error al actualizar ejemplar',
            ], 500);
        }
    }

    public function enviarBiblioteca(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ejemplares' => 'required|array|min:1',
            'ejemplares.*' => 'integer|exists:ejemplares,id',
            'biblioteca_id' => 'required|exists:bibliotecas,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $contexto = $this->resolverContextoUsuario($request->user());
        $bibliotecaDestinoId = (int) $request->biblioteca_id;

        DB::beginTransaction();

        try {
            $ejemplares = Ejemplar::query()
                ->whereIn('id', $request->ejemplares)
                ->lockForUpdate()
                ->get();

            if ($ejemplares->count() !== count($request->ejemplares)) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Uno o mas ejemplares seleccionados no existen.',
                ], 422);
            }

            foreach ($ejemplares as $ejemplar) {
                if (! $this->canMoveEjemplar($ejemplar, $contexto)) {
                    DB::rollBack();

                    return response()->json([
                        'success' => false,
                        'message' => 'Solo puedes mover ejemplares disponibles de tu propia biblioteca.',
                    ], 403);
                }

                if ((int) $ejemplar->biblioteca_id === $bibliotecaDestinoId) {
                    DB::rollBack();

                    return response()->json([
                        'success' => false,
                        'message' => 'El ejemplar ya pertenece a la biblioteca destino.',
                    ], 422);
                }

                $ejemplar->update([
                    'traslado_origen_biblioteca_id' => $ejemplar->biblioteca_id,
                    'traslado_destino_biblioteca_id' => $bibliotecaDestinoId,
                    'estado' => Ejemplar::ESTADO_TRASLADO_PENDIENTE,
                    'estado_traslado' => Ejemplar::TRASLADO_PENDIENTE,
                ]);

                MovimientoEjemplar::create([
                    'ejemplar_id' => $ejemplar->id,
                    'libro_id' => $ejemplar->libro_id,
                    'biblioteca_origen_id' => $ejemplar->biblioteca_id,
                    'biblioteca_destino_id' => $bibliotecaDestinoId,
                    'solicitado_por_user_id' => $request->user()->id,
                    'estado' => MovimientoEjemplar::ESTADO_PENDIENTE,
                    'solicitado_en' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'El traslado quedo pendiente de aceptacion en la biblioteca destino.',
            ]);
        } catch (\Throwable $exception) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'No se pudo registrar el traslado pendiente.',
            ], 500);
        }
    }

    public function resolverTraslado(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:ejemplares,id',
            'accion' => 'required|in:aceptar,rechazar',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $contexto = $this->resolverContextoUsuario($request->user());
            $movimiento = MovimientoEjemplar::query()
                ->where('ejemplar_id', $request->id)
                ->where('estado', MovimientoEjemplar::ESTADO_PENDIENTE)
                ->with('ejemplar')
                ->lockForUpdate()
                ->latest('id')
                ->first();

            if (! $movimiento) {
                throw new \RuntimeException('No se encontro un movimiento pendiente para ese ejemplar.');
            }
            $this->aplicarAccionMovimiento($movimiento, $request->accion, $request->user(), $contexto);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $this->buildTrasladoActionMessage($request->accion, 1),
            ]);
        } catch (\RuntimeException $exception) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        } catch (\Throwable $exception) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'No se pudo responder el traslado del ejemplar.',
            ], 500);
        }
    }

    protected function resolverContextoUsuario(?User $usuario): array
    {
        return $usuario
            ? $this->bibliotecaService->resolverContextoBibliotecas($usuario)
            : [
                'bibliotecasAsignadas' => collect(),
                'accesoGlobal' => false,
            ];
    }

    protected function usuarioPuedeAsignarBiblioteca(?User $usuario, int $bibliotecaId): bool
    {
        $contexto = $this->resolverContextoUsuario($usuario);

        return $contexto['accesoGlobal'] || $contexto['bibliotecasAsignadas']->contains($bibliotecaId);
    }

    protected function canMoveEjemplar(Ejemplar $ejemplar, array $contexto): bool
    {
        if ((int) $ejemplar->estado !== Ejemplar::ESTADO_DISPONIBLE) {
            return false;
        }

        if ((int) $ejemplar->estado_traslado === Ejemplar::TRASLADO_PENDIENTE) {
            return false;
        }

        if ($contexto['accesoGlobal']) {
            return true;
        }

        return $contexto['bibliotecasAsignadas']->contains((int) $ejemplar->biblioteca_id);
    }

    protected function canResolveTransfer(Ejemplar $ejemplar, array $contexto): bool
    {
        if ((int) $ejemplar->estado_traslado !== Ejemplar::TRASLADO_PENDIENTE) {
            return false;
        }

        if ($contexto['accesoGlobal']) {
            return true;
        }

        return $contexto['bibliotecasAsignadas']->contains((int) $ejemplar->traslado_destino_biblioteca_id);
    }

    protected function canEditEjemplar(Ejemplar $ejemplar, array $contexto): bool
    {
        if ((int) $ejemplar->estado_traslado === Ejemplar::TRASLADO_PENDIENTE) {
            return false;
        }

        if ($contexto['accesoGlobal']) {
            return true;
        }

        return $contexto['bibliotecasAsignadas']->contains((int) $ejemplar->biblioteca_id);
    }

    protected function baseTrasladosPendientesQuery(array $contexto)
    {
        $query = MovimientoEjemplar::query()
            ->with(['ejemplar', 'libro', 'bibliotecaOrigen', 'bibliotecaDestino', 'solicitadoPor'])
            ->where('estado', MovimientoEjemplar::ESTADO_PENDIENTE);

        if (! $contexto['accesoGlobal']) {
            if ($contexto['bibliotecasAsignadas']->isEmpty()) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('biblioteca_destino_id', $contexto['bibliotecasAsignadas']->all());
            }
        }

        return $query->latest('id');
    }

    protected function baseTrasladosEnviadosQuery(array $contexto)
    {
        $query = MovimientoEjemplar::query()
            ->with(['ejemplar', 'libro', 'bibliotecaOrigen', 'bibliotecaDestino', 'solicitadoPor'])
            ->where('estado', MovimientoEjemplar::ESTADO_PENDIENTE);

        if (! $contexto['accesoGlobal']) {
            if ($contexto['bibliotecasAsignadas']->isEmpty()) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('biblioteca_origen_id', $contexto['bibliotecasAsignadas']->all());
            }
        }

        return $query->latest('id');
    }

    protected function renderMovimientoCodigo(MovimientoEjemplar $movimiento): string
    {
        $ejemplar = $movimiento->ejemplar;

        if (! $ejemplar) {
            return '<span class="text-muted">Ejemplar no disponible</span>';
        }

        $codigo = trim(implode(' ', array_filter([
            $ejemplar->codigo_dewey ?: $ejemplar->codigo_ant,
            $ejemplar->tipo ? $ejemplar->tipo . ($ejemplar->codigo_interno ?? '') : null,
        ])));

        return '<span class="exemplars-history__code">' . e($codigo ?: 'Sin codigo') . '</span>';
    }

    protected function renderMovimientoUsuario(?string $usuario, $fecha): string
    {
        return '<div class="exemplars-history__user"><strong>'
            . e($usuario ?? 'Usuario no disponible')
            . '</strong><small>'
            . e(optional($fecha)->format('d/m/Y H:i') ?? '-')
            . '</small></div>';
    }

    protected function aplicarAccionMovimiento(MovimientoEjemplar $movimiento, string $accion, User $usuario, array $contexto): void
    {
        if ($movimiento->estado !== MovimientoEjemplar::ESTADO_PENDIENTE) {
            throw new \RuntimeException('El movimiento seleccionado ya fue resuelto.');
        }

        $ejemplar = $movimiento->ejemplar;

        if (! $ejemplar) {
            throw new \RuntimeException('El ejemplar asociado al movimiento ya no existe.');
        }

        match ($accion) {
            'aceptar' => $this->acceptMovimiento($movimiento, $ejemplar, $usuario, $contexto),
            'rechazar' => $this->rejectMovimiento($movimiento, $ejemplar, $usuario, $contexto),
            'cancelar' => $this->cancelMovimiento($movimiento, $ejemplar, $usuario, $contexto),
            default => throw new \RuntimeException('Accion de traslado no valida.'),
        };
    }

    protected function acceptMovimiento(MovimientoEjemplar $movimiento, Ejemplar $ejemplar, User $usuario, array $contexto): void
    {
        if (! $this->canAcceptMovement($movimiento, $contexto)) {
            throw new \RuntimeException('No puedes aceptar este traslado.');
        }

        $ejemplar->biblioteca_id = $movimiento->biblioteca_destino_id;
        $this->finalizeEjemplarTransfer($ejemplar);

        $movimiento->update([
            'estado' => MovimientoEjemplar::ESTADO_ACEPTADO,
            'resuelto_por_user_id' => $usuario->id,
            'resuelto_en' => now(),
        ]);
    }

    protected function rejectMovimiento(MovimientoEjemplar $movimiento, Ejemplar $ejemplar, User $usuario, array $contexto): void
    {
        if (! $this->canAcceptMovement($movimiento, $contexto)) {
            throw new \RuntimeException('No puedes rechazar este traslado.');
        }

        $this->finalizeEjemplarTransfer($ejemplar);

        $movimiento->update([
            'estado' => MovimientoEjemplar::ESTADO_RECHAZADO,
            'resuelto_por_user_id' => $usuario->id,
            'resuelto_en' => now(),
        ]);
    }

    protected function cancelMovimiento(MovimientoEjemplar $movimiento, Ejemplar $ejemplar, User $usuario, array $contexto): void
    {
        if (! $this->canCancelMovement($movimiento, $contexto)) {
            throw new \RuntimeException('No puedes cancelar este traslado.');
        }

        $this->finalizeEjemplarTransfer($ejemplar);

        $movimiento->update([
            'estado' => MovimientoEjemplar::ESTADO_CANCELADO,
            'resuelto_por_user_id' => $usuario->id,
            'resuelto_en' => now(),
        ]);
    }

    protected function finalizeEjemplarTransfer(Ejemplar $ejemplar): void
    {
        $ejemplar->estado = Ejemplar::ESTADO_DISPONIBLE;
        $ejemplar->estado_traslado = Ejemplar::TRASLADO_NINGUNO;
        $ejemplar->traslado_origen_biblioteca_id = null;
        $ejemplar->traslado_destino_biblioteca_id = null;
        $ejemplar->save();
    }

    protected function canAcceptMovement(MovimientoEjemplar $movimiento, array $contexto): bool
    {
        if ($movimiento->estado !== MovimientoEjemplar::ESTADO_PENDIENTE) {
            return false;
        }

        if ($contexto['accesoGlobal']) {
            return true;
        }

        return $contexto['bibliotecasAsignadas']->contains((int) $movimiento->biblioteca_destino_id);
    }

    protected function canCancelMovement(MovimientoEjemplar $movimiento, array $contexto): bool
    {
        if ($movimiento->estado !== MovimientoEjemplar::ESTADO_PENDIENTE) {
            return false;
        }

        if ($contexto['accesoGlobal']) {
            return true;
        }

        return $contexto['bibliotecasAsignadas']->contains((int) $movimiento->biblioteca_origen_id);
    }

    protected function buildTrasladoActionMessage(string $accion, int $cantidad): string
    {
        return match ($accion) {
            'aceptar' => $cantidad === 1 ? 'El traslado fue aceptado.' : 'Los traslados seleccionados fueron aceptados.',
            'rechazar' => $cantidad === 1 ? 'El traslado fue rechazado.' : 'Los traslados seleccionados fueron rechazados.',
            'cancelar' => $cantidad === 1 ? 'El traslado fue cancelado.' : 'Los traslados seleccionados fueron cancelados.',
            default => 'La accion fue completada.',
        };
    }
}
