<?php

namespace App\Providers;

use App\Models\Actividad;
use App\Models\AvisoBiblioteca;
use App\Services\CentroNotificacionesService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $centroNotificaciones = app(CentroNotificacionesService::class);

        View::composer('layouts.biblioteca', function ($view) use ($centroNotificaciones) {
            $user = Auth::user();
            $alerts = $user
                ? $centroNotificaciones->obtenerParaUsuario($user, 6)->values()
                : collect();

            $remainingSlots = max(6 - $alerts->count(), 0);

            if ($remainingSlots > 0 && Schema::hasTable('actividades')) {
                $actividadesQuery = Actividad::with('categoria')
                    ->where('estado', 1);

                if (Schema::hasColumn('actividades', 'destacado')) {
                    $actividadesQuery->orderByDesc('destacado');
                }

                $actividades = $actividadesQuery
                    ->orderBy('fecha_inicio')
                    ->limit(4)
                    ->get()
                    ->map(function ($actividad) {
                        return (object) [
                            'tipo' => 'actividad',
                            'icono' => 'bi-calendar-event',
                            'titulo' => $actividad->titulo,
                            'contenido' => $actividad->resumen ?: \Illuminate\Support\Str::limit(strip_tags((string) $actividad->contenido), 110),
                            'meta' => optional($actividad->fecha_inicio)->format('d/m/Y'),
                            'url' => route('evento'),
                            'destacado' => (bool) ($actividad->destacado ?? false),
                        ];
                    });

                $alerts = $alerts->concat($actividades->take($remainingSlots))->values();
                $remainingSlots = max(6 - $alerts->count(), 0);
            }

            if ($remainingSlots > 0 && Schema::hasTable('aviso_bibliotecas')) {
                $avisos = AvisoBiblioteca::query()
                    ->where('estado', 1)
                    ->where(function ($query) {
                        $query->whereNull('inicio_publicacion')
                            ->orWhere('inicio_publicacion', '<=', now());
                    })
                    ->where(function ($query) {
                        $query->whereNull('fin_publicacion')
                            ->orWhere('fin_publicacion', '>=', now());
                    })
                    ->orderByDesc('es_destacado')
                    ->latest()
                    ->limit(4)
                    ->get()
                    ->map(function ($aviso) {
                        return (object) [
                            'tipo' => $aviso->tipo,
                            'icono' => $aviso->tipo === 'noticia' ? 'bi-megaphone-fill' : 'bi-bell-fill',
                            'titulo' => $aviso->titulo,
                            'contenido' => \Illuminate\Support\Str::limit((string) $aviso->contenido, 110),
                            'meta' => optional($aviso->created_at)->format('d/m/Y'),
                            'url' => $aviso->accion_url ?: route('evento'),
                            'destacado' => (bool) $aviso->es_destacado,
                        ];
                    });

                $alerts = $alerts->concat($avisos->take($remainingSlots))->values();
            }

            $view->with('libraryAlerts', $alerts->take(6)->values());
        });

        View::composer('layouts.admin', function ($view) use ($centroNotificaciones) {
            $user = Auth::user();
            $alerts = $user
                ? $centroNotificaciones->obtenerParaUsuario($user, 6)->values()
                : collect();

            $view->with('adminAlerts', $alerts->take(6)->values());
        });
    }
}