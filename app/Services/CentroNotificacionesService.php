<?php

namespace App\Services;

use App\Models\Actividad;
use App\Models\AvisoBiblioteca;
use App\Models\Notificacion;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CentroNotificacionesService
{
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

        $notificacion = Notificacion::firstOrNew([
            'entidad_tipo' => 'actividad',
            'entidad_id' => $actividad->id,
        ]);

        $notificacion->fill([
            'titulo' => $actividad->titulo,
            'contenido' => $actividad->resumen ?: Str::limit(strip_tags((string) $actividad->contenido), 160),
            'tipo' => 'actividad',
            'canal' => 'interno',
            'audiencia' => 'global',
            'user_id_origen' => $actividad->user_id,
            // Abre directamente el evento en la agenda publica.
            'accion_url' => route('evento', [], false) . '#evento-' . $actividad->id,
            'es_programada' => $actividad->fecha_inicio?->isFuture() ?? false,
            'fecha_expiracion' => ($actividad->fecha_fin ?? $actividad->fecha_inicio)?->copy()->endOfDay(),
            'estado' => 1,
        ]);

        // Editar la actividad no la vuelve a publicar como nueva.
        if (! $notificacion->exists || ! $notificacion->fecha_publicacion) {
            $notificacion->fecha_publicacion = now();
        }

        $notificacion->save();

        return $notificacion;
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
                'url' => $this->urlSegura($notificacion->accion_url),
                'destacado' => $notificacion->tipo === 'critico',
                'leido' => (bool) optional($notificacion->destinatarios->first())->leido,
                'audiencia' => $notificacion->audiencia,
                'entidad' => $notificacion->entidad_tipo ? $notificacion->entidad_tipo . ':' . $notificacion->entidad_id : null,
            ]);
    }

    /**
     * Campana del sitio publico: primero los avisos del usuario; luego actividades vigentes
     * (sin repetir las que ya llegaron como notificacion) y avisos generales.
     * Los visitantes sin sesion solo ven actividades y avisos generales.
     */
    public function paraCampanaBiblioteca(?User $user, int $limite = 6): Collection
    {
        $alertas = $user ? $this->obtenerParaUsuario($user, $limite)->values() : collect();

        if ($alertas->count() < $limite) {
            $notificadas = $alertas->pluck('entidad')->filter()->all();

            $actividades = Actividad::with('categoria')
                ->vigentes()
                ->orderByDesc('destacado')
                ->orderBy('fecha_inicio')
                ->limit($limite)
                ->get()
                ->reject(fn (Actividad $a) => in_array('actividad:' . $a->id, $notificadas, true))
                ->map(fn (Actividad $a) => (object) [
                    'tipo' => 'actividad',
                    'icono' => 'bi-calendar-event',
                    'titulo' => $a->titulo,
                    'contenido' => $a->resumen ?: Str::limit(strip_tags((string) $a->contenido), 110),
                    'meta' => $a->rangoFechas(),
                    'url' => route('evento', [], false) . '#evento-' . $a->id,
                    'destacado' => (bool) $a->destacado,
                    'leido' => false,
                    'audiencia' => 'global',
                    'entidad' => 'actividad:' . $a->id,
                ]);

            $alertas = $alertas->concat($actividades->take($limite - $alertas->count()));
        }

        if ($alertas->count() < $limite) {
            $avisos = AvisoBiblioteca::query()
                ->where('estado', 1)
                ->where(fn ($q) => $q->whereNull('inicio_publicacion')->orWhere('inicio_publicacion', '<=', now()))
                ->where(fn ($q) => $q->whereNull('fin_publicacion')->orWhere('fin_publicacion', '>=', now()))
                ->orderByDesc('es_destacado')
                ->latest()
                ->limit($limite)
                ->get()
                ->map(fn (AvisoBiblioteca $aviso) => (object) [
                    'tipo' => $aviso->tipo,
                    'icono' => $aviso->tipo === 'noticia' ? 'bi-megaphone-fill' : 'bi-bell-fill',
                    'titulo' => $aviso->titulo,
                    'contenido' => Str::limit((string) $aviso->contenido, 110),
                    'meta' => optional($aviso->created_at)->format('d/m/Y'),
                    'url' => $aviso->accion_url ? $this->urlSegura($aviso->accion_url) : route('evento', [], false),
                    'destacado' => (bool) $aviso->es_destacado,
                    'leido' => false,
                    'audiencia' => 'global',
                    'entidad' => null,
                ]);

            $alertas = $alertas->concat($avisos->take($limite - $alertas->count()));
        }

        return $alertas->take($limite)->values();
    }

    protected function perteneceAudiencia(User $user, string $audiencia): bool
    {
        return match ($audiencia) {
            'lectores' => $this->esLector($user) || strtolower(trim((string) $user->tipo_usuario)) === 'lector',
            // Cualquier rol activo que no sea LECTOR (incluye "Atencion a estudiantes - especializadas").
            'admins' => $user->esPersonal(),
            'global' => true,
            default => false,
        };
    }

    // Rol LECTOR activo: estado NULL cuenta como activo (igual que en PermisoPorRuta).
    protected function esLector(User $user): bool
    {
        return $user->usuarioRolBibliotecas()
            ->where('rol_id', User::ROL_LECTOR)
            ->where(fn ($q) => $q->whereNull('estado')->orWhere('estado', '!=', 0))
            ->exists();
    }

    // La campana solo enlaza rutas internas o http(s); cualquier otro esquema (javascript:, data:...) se descarta.
    protected function urlSegura(?string $url): string
    {
        $url = trim((string) $url);

        return preg_match('#^(/(?!/)[^\s]*|https?://[^\s]+)$#i', $url) ? $url : '#';
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