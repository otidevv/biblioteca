<?php

namespace App\Console\Commands;

use App\Models\Ejemplar;
use App\Models\Reservacion;
use App\Models\Sancion;
use App\Services\SancionAutomaticaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProcesarReservasVencidas extends Command
{
    protected $signature = 'reservas:procesar-vencidas';

    protected $description = 'Marca reservas vencidas, libera ejemplares y registra sanciones por no recojo.';

    public function handle(SancionAutomaticaService $servicio): int
    {
        $procesadas = 0;
        $sancionesCreadas = 0;
        $yaSancionadas = 0;

        Reservacion::query()
            ->where('estado', 0)
            ->whereRaw("TIMESTAMP(fecha_limite, '20:00:00') < ?", [now()->format('Y-m-d H:i:s')])
            ->orderBy('id')
            ->chunkById(200, function ($reservas) use ($servicio, &$procesadas, &$sancionesCreadas, &$yaSancionadas) {
                foreach ($reservas as $reserva) {
                    DB::transaction(function () use ($reserva, $servicio, &$procesadas, &$sancionesCreadas, &$yaSancionadas) {
                        $reservaActual = Reservacion::query()->lockForUpdate()->find($reserva->id);

                        if (! $reservaActual || (int) $reservaActual->estado !== 0) {
                            return;
                        }

                        $reservaActual->estado = 3;
                        $reservaActual->save();

                        $ejemplar = Ejemplar::query()->lockForUpdate()->find($reservaActual->ejemplar_id);
                        if ($ejemplar && (int) $ejemplar->estado === 2) {
                            $ejemplar->estado = 1;
                            $ejemplar->save();
                        }

                        $yaExisteSancion = Sancion::query()
                            ->where('reservacion_id', $reservaActual->id)
                            ->exists();

                        if ($yaExisteSancion) {
                            $yaSancionadas++;
                            $procesadas++;

                            return;
                        }

                        if ($servicio->registrarReservaNoRecogida($reservaActual)) {
                            $sancionesCreadas++;
                        }

                        $procesadas++;
                    });
                }
            });

        $mensaje = "Reservas vencidas procesadas: {$procesadas}. Sanciones creadas: {$sancionesCreadas}. Reservas con sancion previa: {$yaSancionadas}.";

        $this->info($mensaje);
        $this->line($mensaje);

        return self::SUCCESS;
    }
}
