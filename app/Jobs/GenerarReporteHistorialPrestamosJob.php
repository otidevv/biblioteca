<?php

namespace App\Jobs;

use App\Models\ReporteGenerado;
use App\Services\ReporteHistorialPrestamosService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

class GenerarReporteHistorialPrestamosJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 300;

    public function __construct(public int $reporteId)
    {
    }

    public function handle(ReporteHistorialPrestamosService $service): void
    {
        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        $reporte = ReporteGenerado::with('user')->find($this->reporteId);

        if (!$reporte || !$reporte->user) {
            return;
        }

        $reporte->update([
            'estado' => 'procesando',
            'error' => null,
        ]);

        try {
            $filtros = is_array($reporte->filtros) ? $reporte->filtros : [];
            $query = $service->consultaReporte($reporte->user, $filtros);
            $totalRegistros = (clone $query)->count();

            Storage::disk('local')->makeDirectory('reportes/historial_prestamos');

            if ($reporte->formato === 'excel') {
                $archivoNombre = 'historial_prestamos_' . $reporte->id . '.xls';
                $archivoRuta = 'reportes/historial_prestamos/' . $archivoNombre;
                $rows = $query->get()->map(function ($registro) use ($service) {
                    $fechaPrestamo = $registro->fecha_prestamo ? Carbon::parse($registro->fecha_prestamo) : null;
                    $fechaDevolucion = $registro->fecha_devolucion ? Carbon::parse($registro->fecha_devolucion) : Carbon::now();
                    $registro->codigo_ejemplar = $this->resolverCodigoEjemplar($registro);
                    $registro->tipo_prestamo_texto = $service->textoTipoPrestamo($registro->prestamo_lugar);
                    $registro->estado_general_texto = $service->textoEstadoGeneral((int) $registro->estado);
                    $registro->estado_prestamo_texto = $service->textoEstadoPrestamo((int) $registro->estado_prestamo);
                    $registro->fecha_prestamo_texto = $fechaPrestamo?->format('d/m/Y H:i') ?: '-';
                    $registro->fecha_limite_texto = $registro->fecha_limite ? Carbon::parse($registro->fecha_limite)->format('d/m/Y') : '-';
                    $registro->fecha_devolucion_texto = $registro->fecha_devolucion ? Carbon::parse($registro->fecha_devolucion)->format('d/m/Y H:i') : '-';
                    $registro->dias_prestado = $fechaPrestamo ? max(1, $fechaPrestamo->copy()->startOfDay()->diffInDays($fechaDevolucion->copy()->startOfDay()) + 1) : null;

                    return $registro;
                });

                $contenidoExcel = View::make('lectores.reportes.historial_prestamos_excel', [
                    'registros' => $rows,
                    'filtrosTexto' => $service->describirFiltros($filtros),
                    'generadoEn' => now(),
                    'anchoColumnas' => $this->calcularAnchosColumnas($rows),
                ])->render();

                Storage::disk('local')->put($archivoRuta, $contenidoExcel);
            } else {
                if ($totalRegistros > 1500) {
                    throw new \RuntimeException('El PDF es demasiado grande para generarse de forma segura. Usa Excel o aplica filtros mas especificos.');
                }

                $archivoNombre = 'historial_prestamos_' . $reporte->id . '.pdf';
                $archivoRuta = 'reportes/historial_prestamos/' . $archivoNombre;
                $rows = $query->get()->map(function ($registro) use ($service) {
                    $fechaPrestamo = $registro->fecha_prestamo ? Carbon::parse($registro->fecha_prestamo) : null;
                    $fechaDevolucion = $registro->fecha_devolucion ? Carbon::parse($registro->fecha_devolucion) : null;
                    $registro->codigo_ejemplar = $this->resolverCodigoEjemplar($registro);
                    $registro->tipo_prestamo_texto = $service->textoTipoPrestamo($registro->prestamo_lugar);
                    $registro->estado_general_texto = $service->textoEstadoGeneral((int) $registro->estado);
                    $registro->estado_prestamo_texto = $service->textoEstadoPrestamo((int) $registro->estado_prestamo);
                    $registro->fecha_prestamo_texto = $fechaPrestamo?->format('d/m/Y H:i') ?: '-';
                    $registro->fecha_limite_texto = $registro->fecha_limite ? Carbon::parse($registro->fecha_limite)->format('d/m/Y') : '-';
                    $registro->fecha_devolucion_texto = $fechaDevolucion?->format('d/m/Y H:i') ?: '-';
                    $registro->dias_prestado = $fechaPrestamo ? max(1, $fechaPrestamo->copy()->startOfDay()->diffInDays(($fechaDevolucion ?? Carbon::now())->copy()->startOfDay()) + 1) : null;
                    return $registro;
                });

                $pdf = Pdf::loadView('lectores.reportes.historial_prestamos_pdf', [
                    'registros' => $rows,
                    'filtrosTexto' => $service->describirFiltros($filtros),
                    'generadoEn' => now(),
                ])->setPaper('a4', 'landscape');

                Storage::disk('local')->put($archivoRuta, $pdf->output());
            }

            $reporte->update([
                'estado' => 'completado',
                'archivo_nombre' => $archivoNombre,
                'archivo_ruta' => $archivoRuta,
                'total_registros' => $totalRegistros,
                'procesado_en' => now(),
                'error' => null,
            ]);
        } catch (\Throwable $e) {
            $reporte->update([
                'estado' => 'fallido',
                'error' => $this->normalizarError($e->getMessage()),
                'procesado_en' => now(),
            ]);
        }
    }

    protected function normalizarError(string $mensaje): string
    {
        $mensaje = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $mensaje) ?: $mensaje;

        return preg_replace('/[^\x20-\x7E]/', '', $mensaje) ?: 'Error al generar el reporte.';
    }

    protected function resolverCodigoEjemplar(object $registro): string
    {
        $codigoDewey = trim((string) ($registro->ejemplar_codigo_dewey ?? ''));
        $codigoAnt = trim((string) ($registro->ejemplar_codigo_ant ?? ''));
        $tipo = trim((string) ($registro->ejemplar_tipo ?? ''));
        $interno = trim((string) ($registro->ejemplar_codigo_interno ?? ''));

        if ($codigoDewey !== '') {
            return $codigoDewey . $tipo . $interno;
        }

        return $codigoAnt !== '' ? $codigoAnt : 'Sin codigo';
    }

    protected function calcularAnchosColumnas(Collection $registros): array
    {
        $anchosBase = [
            1 => 48,
            2 => 180,
            3 => 120,
            4 => 130,
            5 => 90,
            6 => 82,
            7 => 92,
            8 => 96,
            9 => 98,
            10 => 92,
            11 => 98,
            12 => 78,
            13 => 120,
            14 => 140,
            15 => 140,
        ];

        foreach ($registros as $registro) {
            $valores = [
                1 => (string) $registro->id,
                2 => $registro->titulo ?: '-',
                3 => $registro->biblioteca_nombre ?: '-',
                4 => $registro->lector_nombre ?: '-',
                5 => $registro->codigo_ejemplar ?: '-',
                6 => $registro->tipo_prestamo_texto ?: '-',
                7 => $registro->estado_general_texto ?: '-',
                8 => $registro->estado_prestamo_texto ?: '-',
                9 => $registro->fecha_prestamo_texto ?: '-',
                10 => $registro->fecha_limite_texto ?: '-',
                11 => $registro->fecha_devolucion_texto ?: '-',
                12 => (string) ($registro->dias_prestado ?: '-'),
                13 => $registro->bibliotecario_nombre ?: '-',
                14 => $registro->observaciones_prestamo ?: '-',
                15 => $registro->observaciones_devolucion ?: '-',
            ];

            foreach ($valores as $indice => $valor) {
                $longitud = function_exists('mb_strlen') ? mb_strlen((string) $valor) : strlen((string) $valor);
                $anchosBase[$indice] = min(max($anchosBase[$indice], (int) ($longitud * 6.2) + 18), 240);
            }
        }

        return $anchosBase;
    }
}
