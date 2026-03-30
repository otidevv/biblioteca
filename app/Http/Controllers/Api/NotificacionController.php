<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Actividad;
use App\Models\Notificacion;
use App\Models\User;
use App\Services\CentroNotificacionesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class NotificacionController extends Controller
{
    public function listar(Request $request)
    {
        $query = Notificacion::with(['origen', 'destinatarios'])->latest('id');

        return DataTables::eloquent($query)
            ->addColumn('tipo_badge', function ($row) {
                $labels = [
                    'aviso' => 'Aviso',
                    'actividad' => 'Actividad',
                    'recordatorio' => 'Recordatorio',
                    'critico' => 'Critico',
                    'personal' => 'Personal',
                    'disponibilidad_libro' => 'Disponibilidad',
                ];

                return '<span class="badge bg-info">' . ($labels[$row->tipo] ?? ucfirst($row->tipo)) . '</span>';
            })
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

                return $row->destinatarios()->count() . ' usuario(s)';
            })
            ->addColumn('fecha_publicacion_texto', function ($row) {
                return optional($row->fecha_publicacion)->timezone(config('app.timezone'))->format('d-m-Y H:i') ?? '-';
            })
            ->addColumn('fecha_expiracion_texto', function ($row) {
                return optional($row->fecha_expiracion)->timezone(config('app.timezone'))->format('d-m-Y H:i') ?? '-';
            })
            ->addColumn('acciones', function ($row) {
                return '<button class="btn btn-sm btn-primary editarNotificacion">Editar</button>';
            })
            ->rawColumns(['tipo_badge', 'audiencia_badge', 'estado_badge', 'acciones'])
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

            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la notificacion.',
                'error' => $e->getMessage(),
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
            $notificacion->update($payload);

            if (($payload['audiencia'] ?? $notificacion->audiencia) === 'personal') {
                $userIds = collect($data['user_ids'] ?? [])->map(fn ($id) => (int) $id)->filter()->unique()->values()->all();
                $notificacion->destinatarios()->delete();

                foreach ($userIds as $userId) {
                    $notificacion->destinatarios()->create(['user_id' => $userId]);
                }
            } else {
                $notificacion->destinatarios()->delete();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Notificacion actualizada correctamente.',
                'data' => $notificacion->load('destinatarios'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la notificacion.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function recursosFormulario()
    {
        $usuarios = User::query()
            ->select('id', 'name', 'email', 'tipo_usuario')
            ->with(['roles' => function ($query) {
                $query->wherePivot('estado', 1)->select('roles.id', 'nombre');
            }])
            ->orderBy('name')
            ->limit(200)
            ->get();

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
            'usuarios' => $usuarios,
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
            'accion_url' => 'nullable|string|max:255',
            'fecha_publicacion' => 'nullable|date',
            'fecha_expiracion' => 'nullable|date|after_or_equal:fecha_publicacion',
            'estado' => 'required|boolean',
            'actividad_id' => 'nullable|integer',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'integer|exists:users,id',
        ];

        $data = $request->validate($rules);

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
