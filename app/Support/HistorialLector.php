<?php

namespace App\Support;

use App\Models\Ejemplar;
use App\Models\Libro;
use App\Models\Prestamo;
use App\Models\Reservacion;
use App\Models\Sancion;
use Carbon\CarbonInterface;
use Illuminate\Support\Str;

/**
 * Textos y estados para "Mis prestamos" y "Mis reservas" del lector.
 */
class HistorialLector
{
    // "Garcia Marquez, Gabriel y 2 mas"
    public static function autores(?Libro $libro, int $max = 2): string
    {
        $nombres = ($libro?->autores ?? collect())
            ->map(fn ($a) => Str::title(mb_strtolower(trim($a->nombres . ' ' . $a->apellidos))))
            ->filter()
            ->values();

        if ($nombres->count() > $max) {
            return $nombres->take($max)->implode(', ') . ' y ' . ($nombres->count() - $max) . ' más';
        }

        return $nombres->implode(', ');
    }

    public static function codigoEjemplar(?Ejemplar $ejemplar): ?string
    {
        if (! $ejemplar) {
            return null;
        }

        $codigo = trim((string) $ejemplar->codigo_ant);

        if ($codigo !== '') {
            return $codigo;
        }

        return $ejemplar->codigo_interno ? 'Ej. ' . $ejemplar->codigo_interno : null;
    }

    // Los registros anteriores a guardar la hora quedan en 00:00: solo se muestra la fecha.
    public static function fecha(?CarbonInterface $fecha): string
    {
        if (! $fecha) {
            return '—';
        }

        return $fecha->format(self::tieneHora($fecha) ? 'd/m/Y H:i' : 'd/m/Y');
    }

    public static function tieneHora(CarbonInterface $fecha): bool
    {
        return $fecha->format('H:i:s') !== '00:00:00';
    }

    // Cuanto tiempo tuvo el libro (o lleva, si sigue en curso).
    public static function duracion(Prestamo $prestamo): ?string
    {
        $inicio = $prestamo->fecha_prestamo;
        $fin = $prestamo->fecha_devolucion ?? ((int) $prestamo->estado === 1 ? now() : null);

        if (! $inicio || ! $fin) {
            return null;
        }

        if (self::tieneHora($inicio) && ($prestamo->fecha_devolucion === null || self::tieneHora($fin))) {
            $minutos = (int) $inicio->diffInMinutes($fin);

            if ($minutos < 60) {
                return max(1, $minutos) . ' min';
            }

            if ($minutos < 24 * 60) {
                return intdiv($minutos, 60) . ' h' . ($minutos % 60 ? ' ' . ($minutos % 60) . ' min' : '');
            }
        }

        $dias = (int) $inicio->copy()->startOfDay()->diffInDays($fin->copy()->startOfDay());

        return $dias === 0 ? 'El mismo día' : $dias . ' día' . ($dias === 1 ? '' : 's');
    }

    // Retraso de una devolucion tardia o de un prestamo vencido que sigue en curso.
    public static function retraso(Prestamo $prestamo): ?string
    {
        $limite = $prestamo->fecha_limite_real;
        $referencia = $prestamo->fecha_devolucion ?? ((int) $prestamo->estado === 1 ? now() : null);

        if (! $limite || ! $referencia) {
            return null;
        }

        $tardio = (int) $prestamo->estado_prestamo === 2 || ((int) $prestamo->estado === 1 && $referencia->gt($limite));

        if (! $tardio) {
            return null;
        }

        $dias = (int) $limite->copy()->startOfDay()->diffInDays($referencia->copy()->startOfDay());

        if ($dias === 0) {
            return 'Después de las ' . $limite->format('H:i');
        }

        return $dias . ' día' . ($dias === 1 ? '' : 's') . ' de retraso';
    }

    /** @return array{clave: string, texto: string} */
    public static function situacionPrestamo(Prestamo $prestamo): array
    {
        if ((int) $prestamo->estado === 1) {
            return $prestamo->fecha_limite_real && now()->gte($prestamo->fecha_limite_real)
                ? ['clave' => 'vencido', 'texto' => 'Fuera de plazo']
                : ['clave' => 'curso', 'texto' => 'En curso'];
        }

        return match ((int) $prestamo->estado_prestamo) {
            3 => ['clave' => 'deterioro', 'texto' => 'Deterioro registrado'],
            2 => ['clave' => 'tardanza', 'texto' => 'Con tardanza'],
            default => ['clave' => 'a-tiempo', 'texto' => 'A tiempo'],
        };
    }

    /** @return array{clave: string, texto: string} */
    public static function situacionReserva(Reservacion $reserva): array
    {
        return match ((int) $reserva->estado) {
            1 => ['clave' => 'entregada', 'texto' => 'Entregada'],
            2 => ['clave' => 'cancelada', 'texto' => 'Cancelada'],
            3 => ['clave' => 'vencida', 'texto' => 'No recogida'],
            // Pendiente pasada la hora limite: aun no la proceso el sistema, pero ya vencio.
            default => now()->gte($reserva->fecha_limite_real)
                ? ['clave' => 'vencida', 'texto' => 'No recogida']
                : ['clave' => 'pendiente', 'texto' => 'Por recoger'],
        };
    }

    /** @return array{clave: string, texto: string} */
    public static function estadoSancion(Sancion $sancion): array
    {
        if ((int) $sancion->estado !== 1) {
            return ['clave' => 'cerrada', 'texto' => 'Levantada'];
        }

        return $sancion->fecha_fin && $sancion->fecha_fin->gte(today())
            ? ['clave' => 'vigente', 'texto' => 'Vigente hasta el ' . $sancion->fecha_fin->format('d/m/Y')]
            : ['clave' => 'cumplida', 'texto' => 'Cumplida el ' . ($sancion->fecha_fin?->format('d/m/Y') ?? '—')];
    }

    public static function mes(CarbonInterface $fecha): string
    {
        return Str::ucfirst($fecha->locale('es')->translatedFormat('F Y'));
    }
}
