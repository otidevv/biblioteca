<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use App\Models\Ejemplar;
use App\Models\Dewey;
use App\Models\Dewey_aprendizaje;
use App\Models\Prestamo;
use App\Models\Reservacion;
use App\Services\SancionAutomaticaService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('dewey:seed-learning {--reset : Vacia dewey_aprendizajes antes de poblarla}', function () {
    if ($this->option('reset')) {
        Dewey_aprendizaje::query()->delete();
        $this->warn('Tabla dewey_aprendizajes reiniciada.');
    }

    $stopwords = [
        'de', 'del', 'la', 'las', 'el', 'los', 'y', 'e', 'en', 'un', 'una', 'unos', 'unas',
        'por', 'para', 'con', 'sin', 'a', 'al', 'o', 'u', 'general', 'otras', 'otros'
    ];

    $normalizar = function (string $texto) use ($stopwords) {
        $texto = mb_strtolower(trim($texto), 'UTF-8');
        $texto = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
        $texto = preg_replace('/[^a-z0-9\s,;-]/', ' ', $texto);
        $texto = preg_replace('/\s+/', ' ', $texto);

        $tokens = preg_split('/[\s,;]+/', $texto, -1, PREG_SPLIT_NO_EMPTY);

        return collect($tokens)
            ->map(fn ($token) => trim($token))
            ->filter(fn ($token) => strlen($token) >= 3 && !in_array($token, $stopwords, true))
            ->unique()
            ->values();
    };

    $insertados = 0;
    $actualizados = 0;

    Dewey::query()->chunk(200, function ($deweys) use ($normalizar, &$insertados, &$actualizados) {
        foreach ($deweys as $dewey) {
            $tokens = $normalizar($dewey->nombre . ' ' . ($dewey->keywords ?? ''));

            foreach ($tokens as $token) {
                $registro = Dewey_aprendizaje::query()->firstOrNew([
                    'palabra' => $token,
                    'codigo_dewey' => $dewey->codigo,
                ]);

                if ($registro->exists) {
                    $registro->increment('peso');
                    $actualizados++;
                    continue;
                }

                $registro->peso = 1;
                $registro->save();
                $insertados++;
            }
        }
    });

    $this->info("Aprendizaje Dewey generado. Nuevos: {$insertados}. Reforzados: {$actualizados}.");
})->purpose('Puebla dewey_aprendizajes desde nombres y keywords de Dewey');

Artisan::command('sanciones:procesar', function () {
    $servicio = app(SancionAutomaticaService::class);
    $sancionesPrestamo = 0;
    $sancionesReserva = 0;

    Prestamo::query()
        ->where('estado', 1)
        ->chunk(200, function ($prestamos) use ($servicio, &$sancionesPrestamo) {
            foreach ($prestamos as $prestamo) {
                if ($servicio->registrarPorPrestamo($prestamo)) {
                    $sancionesPrestamo++;
                }
            }
        });

    Reservacion::query()
        ->where('estado', 0)
        ->chunk(200, function ($reservas) use ($servicio, &$sancionesReserva) {
            foreach ($reservas as $reserva) {
                $fechaLimiteReal = optional($reserva->fecha_limite_real);

                if (! $fechaLimiteReal || now()->lte($fechaLimiteReal)) {
                    continue;
                }

                DB::transaction(function () use ($reserva, $servicio, &$sancionesReserva) {
                    $reservaActual = Reservacion::lockForUpdate()->find($reserva->id);

                    if (! $reservaActual || (int) $reservaActual->estado !== 0) {
                        return;
                    }

                    $reservaActual->estado = 2;
                    $reservaActual->save();

                    $ejemplar = Ejemplar::lockForUpdate()->find($reservaActual->ejemplar_id);
                    if ($ejemplar && (int) $ejemplar->estado === 2) {
                        $ejemplar->estado = 1;
                        $ejemplar->save();
                    }

                    if ($servicio->registrarReservaNoRecogida($reservaActual)) {
                        $sancionesReserva++;
                    }
                });
            }
        });

    $this->info("Sanciones procesadas. Prestamos: {$sancionesPrestamo}. Reservas: {$sancionesReserva}.");
})->purpose('Aplica sanciones automaticas por tardanza, deterioro y reservas no recogidas');
