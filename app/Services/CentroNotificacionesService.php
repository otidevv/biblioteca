<?php

namespace App\Services;

use App\Models\Notificacion;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CentroNotificacionesService
{
    protected const ROLES_LECTOR = [
        'LECTOR',
    ];

    protected const ROLES_PERSONAL = [
        'PROGRAMADOR',
        'ADMINISTRADOR',
        'ENCARGADO',
        'ATENCION A ESTUDIANTES',
    ];

    public function crearPorAudiencia(array $data, string $audiencia = 'admins'): Notificacion
    {
        return Notificacion::create([
            'titulo' => $data['titulo'],
            'contenido' => $data['contenido'],
            'tipo' => $data['tipo'] ?? 'aviso',
            'canal' => $data['canal'] ?? 'interno',
            'audiencia' => $audiencia,
            'user_id_origen' => $data['user_id_origen'] ?? null,
            'accion_url' => $data['accion_url'] ?? null,
            'entidad_tipo' => $data['entidad_tipo'] ?? null,
            'entidad_id' => $data['entidad_id'] ?? null,
            'es_programada' => $data['es_programada'] ?? false,
            'fecha_publicacion' => $data['fecha_publicacion'] ?? now(),
            'fecha_expiracion' => $data['fecha_expiracion'] ?? null,
            'estado' => $data['estado'] ?? 1,
        ]);
    }

    public function crearPersonal(User $user, array $data): Notificacion
    {
        $notificacion = $this->crearPorAudiencia($data, 'personal');

        $notificacion->destinatarios()->create([
            'user_id' => $user->id,
        ]);

        return $notificacion;
    }

    public function sincronizarActividad(\App\Models\Actividad $actividad): ?Notificacion
    {
        if ((int) $actividad->estado !== 1) {
            $notificacion = Notificacion::query()
                ->where('entidad_tipo', 'actividad')
                ->where('entidad_id', $actividad->id)
                ->first();

            if ($notificacion) {
                $notificacion->update(['estado' => 0]);
            }

            return $notificacion;
        }

        return Notificacion::updateOrCreate(
            [
                'entidad_tipo' => 'actividad',
                'entidad_id' => $actividad->id,
            ],
            [
                'titulo' => $actividad->titulo,
                'contenido' => $actividad->resumen ?: Str::limit(strip_tags((string) $actividad->contenido), 160),
                'tipo' => 'actividad',
                'canal' => 'interno',
                'audiencia' => 'global',
                'user_id_origen' => $actividad->user_id,
                'accion_url' => route('evento'),
                'es_programada' => optional($actividad->fecha_inicio)?->isFuture() ?? false,
                'fecha_publicacion' => now(),
                'fecha_expiracion' => optional($actividad->fecha_fin)?->endOfDay(),
                'estado' => 1,
            ]
        );
    }

    public function obtenerParaUsuario(?User $user, int $limit = 6): Collection
    {
        if (!$user || !Schema::hasTable('notificaciones') || !Schema::hasTable('notificacion_destinatarios')) {
            return collect();
        }

        $query = Notificacion::query()
            ->with(['destinatarios' => function ($query) use ($user) {
                $query->where('user_id', $user->id);
            }])
            ->where('estado', 1)
            ->where(function ($query) {
                $query->whereNull('fecha_publicacion')
                    ->orWhere('fecha_publicacion', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('fecha_expiracion')
                    ->orWhere('fecha_expiracion', '>=', now());
            })
            ->where(function ($query) use ($user) {
                $query->whereHas('destinatarios', function ($subQuery) use ($user) {
                    $subQuery->where('user_id', $user->id)->where('archivado', false);
                });

                if ($this->perteneceAudiencia($user, 'lectores')) {
                    $query->orWhereIn('audiencia', ['lectores', 'global']);
                }

                if ($this->perteneceAudiencia($user, 'admins')) {
                    $query->orWhereIn('audiencia', ['admins', 'global']);
                }
            })
            ->latest('fecha_publicacion')
            ->latest();

        return $query
            ->limit($limit)
            ->get()
            ->map(fn (Notificacion $notificacion) => (object) [
                'tipo' => $notificacion->tipo,
                'icono' => $this->resolverIcono($notificacion->tipo),
                'titulo' => $notificacion->titulo,
                'contenido' => $notificacion->contenido,
                'meta' => optional($notificacion->fecha_publicacion ?? $notificacion->created_at)?->diffForHumans(),
                'url' => $notificacion->accion_url ?: '#',
                'destacado' => $notificacion->tipo === 'critico',
                'leido' => (bool) optional($notificacion->destinatarios->first())->leido,
                'audiencia' => $notificacion->audiencia,
            ]);
    }

    protected function perteneceAudiencia(User $user, string $audiencia): bool
    {
        return match ($audiencia) {
            'lectores' => $this->tieneAlgunRol($user, self::ROLES_LECTOR) || strtolower(trim((string) $user->tipo_usuario)) === 'lector',
            'admins' => $this->tieneAlgunRol($user, self::ROLES_PERSONAL) || $this->esTipoUsuarioInterno($user),
            'global' => true,
            default => false,
        };
    }

    protected function tieneAlgunRol(User $user, array $rolesEsperados): bool
    {
        return $this->rolesActivosUsuario($user)->intersect($rolesEsperados)->isNotEmpty();
    }

    protected function rolesActivosUsuario(User $user): Collection
    {
        return $user->roles()
            ->wherePivot('estado', 1)
            ->pluck('nombre')
            ->map(fn ($nombre) => Str::upper(trim((string) $nombre)))
            ->filter()
            ->unique()
            ->values();
    }

    protected function esTipoUsuarioInterno(User $user): bool
    {
        $tipo = Str::upper(trim((string) $user->tipo_usuario));

        return $tipo !== '' && $tipo !== 'LECTOR';
    }

    protected function resolverIcono(string $tipo): string
    {
        return match ($tipo) {
            'critico' => 'bi-exclamation-triangle-fill',
            'disponibilidad_libro' => 'bi-book-half',
            'personal' => 'bi-person-badge-fill',
            'recordatorio' => 'bi-clock-history',
            default => 'bi-bell-fill',
        };
    }
}