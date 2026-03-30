<?php

namespace App\Jobs;

use App\Models\ReporteGenerado;
use App\Services\ReporteInventarioFisicoService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

class GenerarReporteInventarioFisicoJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 300;

    public function __construct(public int $reporteId)
    {
    }

    public function handle(ReporteInventarioFisicoService $service): void
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
            $bibliotecaId = $reporte->filtros['biblioteca_id'] ?? null;
            $query = $service->consultaReporte($reporte->user, $bibliotecaId);
            $totalRegistros = (clone $query)->count();
            $biblioteca = $service->resolverNombreBibliotecaReporte($reporte->user, $bibliotecaId);

            Storage::disk('local')->makeDirectory('reportes/inventario_fisico');

            if ($reporte->formato === 'excel') {
                $archivoNombre = 'inventario_fisico_' . $reporte->id . '.xls';
                $archivoRuta = 'reportes/inventario_fisico/' . $archivoNombre;
                $registros = $query->get();
                $contenidoExcel = View::make('inventario.reportes.fisico_excel', [
                    'registros' => $registros,
                    'biblioteca' => $biblioteca,
                    'generadoEn' => now(),
                    'anchoColumnas' => $this->calcularAnchosColumnas($registros),
                    'totalEjemplares' => (int) $registros->sum('total_ejemplares'),
                ])->render();

                Storage::disk('local')->put($archivoRuta, $contenidoExcel);
            } else {
                if ($totalRegistros > 1500) {
                    throw new \RuntimeException('El PDF es demasiado grande para generarse de forma segura. Usa Excel o aplica un filtro más específico.');
                }

                $archivoNombre = 'inventario_fisico_' . $reporte->id . '.pdf';
                $archivoRuta = 'reportes/inventario_fisico/' . $archivoNombre;
                $rows = $query->get();

                $pdf = Pdf::loadView('inventario.reportes.fisico_pdf', [
                    'registros' => $rows,
                    'biblioteca' => $biblioteca,
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

    protected function calcularAnchosColumnas(Collection $registros): array
    {
        $anchosBase = [
            1 => 36,
            2 => 110,
            3 => 60,
            4 => 52,
            5 => 82,
            6 => 96,
            7 => 180,
            8 => 150,
            9 => 78,
            10 => 92,
            11 => 76,
            12 => 68,
            13 => 90,
            14 => 92,
            15 => 64,
            16 => 108,
            17 => 78,
            18 => 132,
        ];

        foreach ($registros as $registro) {
            $valores = [
                2 => $registro->materias ?: '-',
                3 => $registro->codigo_dewey ?: '-',
                4 => (string) ((int) $registro->total_ejemplares),
                5 => $registro->codigo ?: '-',
                6 => $registro->codigo_ant ?: '-',
                7 => $registro->titulo ?: '-',
                8 => $registro->autores ?: '-',
                9 => $registro->anio_edicion ?: '-',
                10 => (string) ((int) $registro->total_ejemplares),
                11 => $registro->idioma_nombre ?: '-',
                12 => $registro->edicion ?: '-',
                13 => $registro->isbn ?: '-',
                14 => $registro->lugar_publicacion ?: '-',
                15 => (string) ($registro->paginas ?: '-'),
                16 => $registro->editorial_nombre ?: '-',
                17 => (string) ($registro->fecha_publicacion ?: ($registro->anio_edicion ?: '-')),
                18 => $registro->anotaciones ?: '-',
            ];

            foreach ($valores as $indice => $valor) {
                $longitud = function_exists('mb_strlen') ? mb_strlen((string) $valor) : strlen((string) $valor);
                $anchosBase[$indice] = min(max($anchosBase[$indice], (int) ($longitud * 6.2) + 18), 240);
            }
        }

        return $anchosBase;
    }
}
