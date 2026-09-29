<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Actividad;
use App\Models\Notificacion;
use App\Models\User;
use App\Services\CentroNotificacionesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class NotificacionController extends Controller
{
    public function listar(Request $request)
    {
        $query = Notificacion::with(['origen', 'destinatarios.user:id,name,email'])->latest('id');
        $ahora = now();

        $resumen = [
            'publicadas' => Notificacion::where('estado', 1)
                ->where(fn ($q) => $q->whereNull('fecha_publicacion')->orWhere('fecha_publicacion', '<=', $ahora))
                ->where(fn ($q) => $q->whereNull('fecha_expiracion')->orWhere('fecha_expiracion', '>=', $ahora))
                ->count(),
            'programadas' => Notificacion::where('estado', 1)->where('fecha_publicacion', '>', $ahora)->count(),
            'expiradas' => Notificacion::where('estado', 1)->where('fecha_expiracion', '<', $ahora)->count(),
            'inactivas' => Notificacion::where('estado', 0)->count(),
        ];

        return DataTables::eloquent($query)
            ->with('resumen', $resumen)
            ->addColumn('tipo_badge', function ($row) {
                $labels = [
                    'aviso' => 'Aviso',
                    'actividad' => 'Actividad',
                    'recordatorio' => 'Recordatorio',
                    'critico' => 'Crítico',
                    'personal' => 'Personal',
                    'disponibilidad_libro' => 'Disponibilidad',
                ];

                return '<span class="notifications-type notifications-type--' . e($row->tipo) . '">' . e($labels[$row->tipo] ?? ucfirst($row->tipo)) . '</span>';
            })
            ->addColumn('vigencia', function ($row) use ($ahora) {
                [$clase, $texto] = match (true) {
                    (int) $row->estado !== 1 => ['is-off', 'Inactiva'],
                    $row->fecha_publicacion && $row->fecha_publicacion->gt($ahora) => ['is-scheduled', 'Programada'],
                    $row->fecha_expiracion && $row->fecha_expiracion->lt($ahora) => ['is-expired', 'Expirada'],
                    default => ['is-live', 'Publicada'],
                };

                return '<span class="notifications-state ' . $clase . '">' . $texto . '</span>';
            })
            // El formulario usa hora local; el JSON del modelo viene en UTC.
            ->addColumn('fecha_publicacion_input', fn ($row) => $row->fecha_publicacion?->format('Y-m-d\TH:i'))
            ->addColumn('fecha_expiracion_input', fn ($row) => $row->fecha_expiracion?->format('Y-m-d\TH:i'))
            ->addColumn('destinatarios_opciones', fn ($row) => $row->destinatarios->map(fn ($d) => [
                'id' => $d->user_id,
                'text' => $d->user ? $d->user->name . ' - ' . $d->user->email : 'Usuario #' . $d->user_id,
            ])->values())
            ->addColumn('audiencia_badge', function ($row) {
                $labels = [
                    'admins' => 'Personal interno',
                    'lectores' => 'Lectores',
                    'personal' => 'Personal',
                    'global' => 'Global',
                ];

                return '<span class="badge bg-warning">' . ($labels[$row->audiencia] ?? ucfirst($row->audiencia)) . '</span>';
            })
            ->addColumn('estado_badge', function ($row) {
                return '<span class="badge ' . ((int) $row->estado === 1 ? 'bg-success' : 'bg-secondary') . '">' .
                    ((int) $row->estado === 1 ? 'Activa' : 'Inactiva') .
                    '</span>';
            })
            ->addColumn('destino', function ($row) {
                if ($row->audiencia !== 'personal') {
                    $segmentos = [
                        'admins' => 'Personal interno con roles operativos',
                        'lectores' => 'Usuarios con rol lector',
                        'global' => 'Todos los usuarios',
                    ];

                    return 'Segmento: ' . ($segmentos[$row->audiencia] ?? ucfirst($row->audiencia));
                }

                $total = $row->destinatarios->count();
                $leidas = $row->destinatarios->where('leido', true)->count();

                return $total . ' usuario' . ($total === 1 ? '' : 's') . ' · ' . $leidas . ' leída' . ($leidas === 1 ? '' : 's');
            })
            // optional(null)->timezone() devolvia null y ->format() rompia el listado sin fecha de expiracion.
            ->addColumn('fecha_publicacion_texto', fn ($row) => $row->fecha_publicacion?->format('d/m/Y H:i') ?? '-')
            ->addColumn('fecha_expiracion_texto', fn ($row) => $row->fecha_expiracion?->format('d/m/Y H:i') ?? 'Sin expiración')
            ->addColumn('acciones', function ($row) {
                return '<button class="btn btn-sm btn-primary editarNotificacion">Editar</button>';
            })
            ->rawColumns(['tipo_badge', 'audiencia_badge', 'estado_badge', 'vigencia', 'acciones'])
            ->make(true);
    }

    public function nuevo(Request $request, CentroNotificacionesService $centroNotificaciones)
    {
        $data = $this->validar($request);

        DB::beginTransaction();
        try {
            $payload = $this->normalizarPayload($data, $request);
            $notificacion = $payload['audiencia'] === 'personal'
                ? $this->crearPersonal($payload, $data['user_ids'] ?? [], $centroNotificaciones)
                : $centroNotificaciones->crearPorAudiencia($payload, $payload['audiencia']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Notificacion registrada correctamente.',
                'data' => $notificacion->load('destinatarios'),
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Error al registrar la notificacion.', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la notificacion.',
            ], 500);
        }
    }

    public function edit(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:notificaciones,id',
        ]);

        $data = $this->validar($request, true);

        DB::beginTransaction();
        try {
            $notificacion = Notificacion::findOrFail($request->id);
            $payload = $this->normalizarPayload($data, $request);
            unset($payload['user_id_origen']);   // se conserva quien la creo
            $notificacion->update($payload);

            // Sincroniza sin perder quien ya la leyo.
            $userIds = ($payload['audiencia'] ?? $notificacion->audiencia) === 'personal'
                ? collect($data['user_ids'] ?? [])->map(fn ($id) => (int) $id)->filter()->unique()->values()
                : collect();

            $notificacion->destinatarios()->whereNotIn('user_id', $userIds->all())->delete();
            $existentes = $notificacion->destinatarios()->pluck('user_id')->all();

            foreach ($userIds->diff($existentes) as $userId) {
                $notificacion->destinatarios()->create(['user_id' => $userId]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Notificacion actualizada correctamente.',
                'data' => $notificacion->load('destinatarios'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Error al actualizar la notificacion.', ['id' => $request->id, 'error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la notificacion.',
            ], 500);
        }
    }

    // Busqueda de destinatarios para select2 (antes se cargaban solo los primeros 200 usuarios).
    public function buscarUsuarios(Request $request)
    {
        $busqueda = trim((string) $request->input('q', ''));

        $usuarios = User::query()
            ->select('id', 'name', 'email', 'tipo_usuario', 'persona_id')
            ->with(['usuarioRolBibliotecas:id,user_id,rol_id,estado', 'persona:id,dni'])
            ->when($busqueda !== '', function ($query) use ($busqueda) {
                $query->where(function ($sub) use ($busqueda) {
                    $sub->where('name', 'like', '%' . $busqueda . '%')
                        ->orWhere('email', 'like', '%' . $busqueda . '%')
                        ->orWhereHas('persona', fn ($p) => $p->where('dni', 'like', '%' . $busqueda . '%'));
                });
            })
            ->orderBy('name')
            ->limit(30)
            ->get();

        $grupos = ['Personal interno' => [], 'Lectores' => [], 'Otros usuarios' => []];

        foreach ($usuarios as $usuario) {
            $activos = $usuario->usuarioRolBibliotecas->filter(fn ($r) => $r->estado === null || (int) $r->estado !== 0);
            $grupo = match (true) {
                $activos->contains(fn ($r) => (int) $r->rol_id !== User::ROL_LECTOR) => 'Personal interno',
                $activos->contains(fn ($r) => (int) $r->rol_id === User::ROL_LECTOR) => 'Lectores',
                default => 'Otros usuarios',
            };

            $grupos[$grupo][] = [
                'id' => $usuario->id,
                'text' => $usuario->name . ' - ' . $usuario->email . ($usuario->persona?->dni ? ' · DNI ' . $usuario->persona->dni : ''),
            ];
        }

        return response()->json([
            'results' => collect($grupos)
                ->filter()
                ->map(fn ($children, $text) => ['text' => $text, 'children' => $children])
                ->values(),
        ]);
    }

    public function recursosFormulario()
    {
        $actividades = collect();
        if (Schema::hasTable('actividades')) {
            $actividades = Actividad::query()
                ->select('id', 'titulo', 'fecha_inicio')
                ->latest('fecha_inicio')
                ->limit(50)
                ->get();
        }

        return response()->json([
            'success' => true,
            'actividades' => $actividades,
        ]);
    }

    protected function validar(Request $request, bool $editing = false): array
    {
        $rules = [
            'titulo' => 'required|string|max:255',
            'contenido' => 'required|string',
            'tipo' => 'required|string|max:100',
            'audiencia' => 'required|in:admins,lectores,personal,global',
            // Ruta interna (/evento) o enlace http(s); nunca javascript: u otros esquemas.
            'accion_url' => ['nullable', 'string', 'max:255', 'regex:#^(/(?!/)[^\s]*|https?://[^\s]+)$#i'],
            'fecha_publicacion' => 'nullable|date',
            'fecha_expiracion' => 'nullable|date|after_or_equal:fecha_publicacion',
            'estado' => 'required|boolean',
            'actividad_id' => 'nullable|integer',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'integer|exists:users,id',
        ];

        $data = $request->validate($rules, [
            'accion_url.regex' => 'El enlace debe ser una ruta interna (por ejemplo /evento) o una dirección http(s).',
        ]);

        if (($data['audiencia'] ?? null) === 'personal' && empty($data['user_ids'])) {
            throw ValidationException::withMessages([
                'user_ids' => ['Selecciona al menos un destinatario para el mensaje personal.'],
            ]);
        }

        return $data;
    }

    protected function normalizarPayload(array $data, Request $request): array
    {
        $payload = [
            'titulo' => $data['titulo'],
            'contenido' => $data['contenido'],
            'tipo' => $data['tipo'],
            'canal' => 'interno',
            'audiencia' => $data['audiencia'],
            'user_id_origen' => auth()->id(),
            'accion_url' => $data['accion_url'] ?? null,
            'fecha_publicacion' => $data['fecha_publicacion'] ?? now(),
            'fecha_expiracion' => $data['fecha_expiracion'] ?? null,
            'estado' => (int) $data['estado'],
            'es_programada' => !empty($data['fecha_publicacion']) && $data['fecha_publicacion'] > now()->toDateTimeString(),
        ];

        if (!empty($data['actividad_id']) && Schema::hasTable('actividades')) {
            $actividad = Actividad::find($data['actividad_id']);
            if ($actividad) {
                $payload['entidad_tipo'] = 'actividad';
                $payload['entidad_id'] = $actividad->id;
                $payload['accion_url'] = route('evento');

                if ($payload['tipo'] === 'actividad') {
                    $payload['titulo'] = $payload['titulo'] ?: $actividad->titulo;
                    $payload['contenido'] = $payload['contenido'] ?: ($actividad->resumen ?: strip_tags((string) $actividad->contenido));
                }
            }
        }

        return $payload;
    }

    protected function crearPersonal(array $payload, array $userIds, CentroNotificacionesService $centroNotificaciones): Notificacion
    {
        $notificacion = $centroNotificaciones->crearPorAudiencia($payload, 'personal');

        foreach (collect($userIds)->map(fn ($id) => (int) $id)->filter()->unique() as $userId) {
            $notificacion->destinatarios()->create([
                'user_id' => $userId,
            ]);
        }

        return $notificacion;
    }
}
