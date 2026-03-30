<?php

namespace App\Services;

use App\Models\Prestamo;
use App\Models\ReglaSancion;
use App\Models\Reservacion;
use App\Models\Sancion;
use App\Models\TipoSancion;
use Carbon\Carbon;

class SancionAutomaticaService
{
    public function previsualizarPorPrestamo(Prestamo $prestamo, int $estadoLibro = 1, ?int $diasRetraso = null): array
    {
        $diasAtraso = $diasRetraso ?? $this->diasTardanzaPrestamo($prestamo);
        $sanciones = [];

        if ($estadoLibro !== 1) {
            $previewDeterioro = $this->resolverPrevisualizacion(
                evento: 'devolucion_deterioro',
                cantidad: null,
                motivoFallback: 'Se aplicara una sancion automatica por devolucion con deterioro.'
            );

            if (($previewDeterioro['aplica'] ?? false) === true) {
                $sanciones[] = $previewDeterioro;
            }
        }

        if ($diasAtraso >= 1) {
            $previewTardanza = $this->resolverPrevisualizacion(
                evento: 'prestamo_tardio',
                cantidad: $diasAtraso,
                motivoFallback: 'Se aplicara una sancion automatica por devolucion fuera de plazo.'
            );

            if (($previewTardanza['aplica'] ?? false) === true) {
                $sanciones[] = $previewTardanza;
            }
        }

        if (empty($sanciones)) {
            return [
                'aplica' => false,
                'motivo' => 'La devolucion esta dentro del plazo y no registra deterioro.',
            ];
        }

        return [
            'aplica' => true,
            'multiple' => count($sanciones) > 1,
            'sanciones' => $sanciones,
        ];
    }

    public function registrarPorPrestamo(Prestamo $prestamo): array
    {
        $sanciones = [];

        if ((int) $prestamo->estado_libro !== 1 || (int) $prestamo->estado_prestamo === 3) {
            $sancionDeterioro = $this->crearOActualizarDesdeRegla(
                evento: 'devolucion_deterioro',
                userId: $prestamo->lector_id,
                prestamoId: $prestamo->id,
                reservacionId: null,
                bibliotecarioId: $prestamo->user_id,
                fechaBase: Carbon::parse($prestamo->fecha_devolucion ?? now()),
                cantidad: null,
                motivoFallback: 'Sancion automatica por devolucion con deterioro.'
            );

            if ($sancionDeterioro) {
                $sanciones[] = $sancionDeterioro;
            }
        }

        $diasAtraso = $this->diasTardanzaPrestamo($prestamo);

        if ($diasAtraso >= 1) {
            $sancionTardanza = $this->crearOActualizarDesdeRegla(
                evento: 'prestamo_tardio',
                userId: $prestamo->lector_id,
                prestamoId: $prestamo->id,
                reservacionId: null,
                bibliotecarioId: $prestamo->user_id,
                fechaBase: Carbon::parse($prestamo->fecha_devolucion ?? now()),
                cantidad: $diasAtraso,
                motivoFallback: 'Sancion automatica por devolucion fuera de plazo.'
            );

            if ($sancionTardanza) {
                $sanciones[] = $sancionTardanza;
            }
        }

        return $sanciones;
    }

    public function registrarReservaNoRecogida(Reservacion $reservacion): ?Sancion
    {
        $fechaBase = Carbon::parse($reservacion->fecha_limite ?? now());
        $diasExceso = max($fechaBase->copy()->setTime(20, 0, 0)->diffInDays(now(), false), 0);

        return $this->crearOActualizarDesdeRegla(
            evento: 'reserva_no_recogida',
            userId: $reservacion->lector_id,
            prestamoId: null,
            reservacionId: $reservacion->id,
            bibliotecarioId: $reservacion->bibliotecario_id,
            fechaBase: now(),
            cantidad: max(1, $diasExceso),
            motivoFallback: 'Sancion automatica por reserva no recogida.'
        );
    }

    protected function crearOActualizarDesdeRegla(
        string $evento,
        int $userId,
        ?int $prestamoId,
        ?int $reservacionId,
        ?int $bibliotecarioId,
        Carbon $fechaBase,
        ?int $cantidad,
        string $motivoFallback
    ): ?Sancion {
        ['regla' => $regla, 'tipo' => $tipo, 'duracion' => $duracion] = $this->resolverConfiguracion($evento, $cantidad);

        if (! $tipo) {
            return null;
        }

        $fechaInicio = $fechaBase->copy()->startOfDay();
        $fechaFin = $duracion ? $fechaBase->copy()->addDays($duracion)->startOfDay() : null;

        $sancion = Sancion::firstOrNew([
            'user_id' => $userId,
            'prestamo_id' => $prestamoId,
            'reservacion_id' => $reservacionId,
            'tipo' => $tipo->codigo,
        ]);

        $sancion->fill([
            'tipo_sancion_id' => $tipo->id,
            'motivo' => $tipo->nombre ?: $motivoFallback,
            'fecha_inicio' => $fechaInicio->toDateString(),
            'fecha_fin' => $fechaFin?->toDateString(),
            'duracion' => $duracion,
            'observaciones' => $motivoFallback,
            'bibliotecario_id' => $bibliotecarioId,
            'estado' => 1,
        ]);
        $sancion->save();

        return $sancion;
    }

    protected function resolverPrevisualizacion(string $evento, ?int $cantidad, string $motivoFallback): array
    {
        ['regla' => $regla, 'tipo' => $tipo, 'duracion' => $duracion] = $this->resolverConfiguracion($evento, $cantidad);

        if (! $tipo) {
            return [
                'aplica' => false,
                'motivo' => 'No existe una regla o tipo de sancion configurado para este caso.',
            ];
        }

        return [
            'aplica' => true,
            'evento' => $evento,
            'codigo' => $tipo->codigo,
            'nombre' => $tipo->nombre,
            'descripcion' => $tipo->descripcion ?: $motivoFallback,
            'duracion_dias' => $duracion,
            'monto' => $regla?->monto ?? $tipo->monto,
            'requiere_pago' => (bool) $tipo->requiere_pago,
            'bloquea_prestamos' => (bool) $tipo->bloquea_prestamos,
            'motivo' => $motivoFallback,
        ];
    }

    protected function resolverConfiguracion(string $evento, ?int $cantidad): array
    {
        $regla = ReglaSancion::with('tipoSancion')
            ->where('evento', $evento)
            ->where('estado', true)
            ->when($cantidad !== null, function ($query) use ($cantidad) {
                $query->where(function ($sub) use ($cantidad) {
                    $sub->whereNull('dias_desde')->orWhere('dias_desde', '<=', $cantidad);
                })->where(function ($sub) use ($cantidad) {
                    $sub->whereNull('dias_hasta')->orWhere('dias_hasta', '>=', $cantidad);
                });
            })
            ->orderByDesc('dias_desde')
            ->first();

        $tipo = $regla?->tipoSancion;
        $duracion = $regla?->duracion_dias ?? $tipo?->dias_duracion;

        if (! $tipo) {
            $tipo = TipoSancion::where('codigo', match ($evento) {
                'prestamo_tardio' => 'PRESTAMO_TARDANZA',
                'devolucion_deterioro' => 'PRESTAMO_DETERIORO',
                'reserva_no_recogida' => 'RESERVA_NO_RECOGIDA',
                default => null,
            })->first();

            $duracion = $duracion ?? $tipo?->dias_duracion;
        }

        return [
            'regla' => $regla,
            'tipo' => $tipo,
            'duracion' => $duracion,
        ];
    }

    protected function diasTardanzaPrestamo(Prestamo $prestamo): int
    {
        $fechaLimite = $prestamo->fecha_limite_real ?? Carbon::parse($prestamo->fecha_limite)->setTime(20, 0, 0);
        $fechaComparacion = Carbon::parse($prestamo->fecha_devolucion ?? now());

        if ($fechaComparacion->lessThanOrEqualTo($fechaLimite)) {
            return 0;
        }

        return max(1, $fechaLimite->diffInDays($fechaComparacion));
    }
}
