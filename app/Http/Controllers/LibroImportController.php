<?php

namespace App\Http\Controllers;

use App\Imports\LibrosImport;
use App\Models\Biblioteca;
use App\Models\Tipo_registro;
use App\Http\Requests\ImportarLibrosRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LibroImportController extends Controller
{
    public function create()
    {
        $bibliotecas = Biblioteca::query()
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        $tipoRegistros = Tipo_registro::query()
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return view('administracion.libros_importar', compact('bibliotecas', 'tipoRegistros'));
    }

    public function store(ImportarLibrosRequest $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();
        $temporaryFilePath = null;

        try {
            @ini_set('max_execution_time', '0');
            @set_time_limit(0);
            @ini_set('memory_limit', '1024M');

            $temporaryPath = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
                . DIRECTORY_SEPARATOR
                . 'sistema-biblioteca-laravel-excel';

            File::ensureDirectoryExists($temporaryPath);

            config([
                'excel.temporary_files.local_path' => $temporaryPath,
            ]);

            $uploadedFile = $validated['archivo'];
            $temporaryFilePath = $temporaryPath
                . DIRECTORY_SEPARATOR
                . Str::uuid()
                . '.'
                . $uploadedFile->getClientOriginalExtension();

            File::copy($uploadedFile->getRealPath(), $temporaryFilePath);

            $import = new LibrosImport(
                (int) $validated['biblioteca_id'],
                (int) $validated['tipo_registro_id']
            );
            $this->processSpreadsheetInBatches($temporaryFilePath, $import);
        } catch (\Throwable $e) {
            if ($temporaryFilePath && File::exists($temporaryFilePath)) {
                File::delete($temporaryFilePath);
            }

            $payload = [
                'success' => false,
                'message' => 'No fue posible completar la importacion.',
                'errors' => [],
                'exception' => [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ],
            ];

            if ($request->expectsJson()) {
                return response()->json($payload, 500);
            }

            return redirect()
                ->route('administracion.libros.importar')
                ->withInput()
                ->withErrors([
                    'archivo' => $payload['message'],
                ])
                ->with('import_exception', $payload['exception']);
        }

        if ($temporaryFilePath && File::exists($temporaryFilePath)) {
            File::delete($temporaryFilePath);
        }

        $payload = [
            'success' => true,
            'message' => 'La importacion de libros finalizo.',
            'biblioteca_importada_id' => (int) $validated['biblioteca_id'],
            'tipo_registro_importado_id' => (int) $validated['tipo_registro_id'],
            'summary' => $import->getSummary(),
            'errors' => $import->getErrors(),
            'inserted_books' => $import->getInsertedBooks(),
            'omitted_books' => $import->getOmittedBooks(),
        ];

        if ($request->expectsJson()) {
            return response()->json($payload);
        }

        return redirect()->route('administracion.libros.importar')->with([
            'success' => $payload['message'],
            'biblioteca_importada_id' => $payload['biblioteca_importada_id'],
            'tipo_registro_importado_id' => $payload['tipo_registro_importado_id'],
            'import_summary' => $payload['summary'],
            'import_errors' => $payload['errors'],
            'inserted_books' => $payload['inserted_books'],
            'omitted_books' => $payload['omitted_books'],
        ]);
    }

    protected function processSpreadsheetInBatches(string $path, LibrosImport $import, int $batchSize = 500): void
    {
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);

        $spreadsheet = $reader->load($path);
        $sheet = $spreadsheet->getSheet(0);
        $headers = $this->extractHeaders($sheet);

        if ($headers === []) {
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
            return;
        }

        $batch = collect();

        foreach ($sheet->getRowIterator(2) as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $assoc = [];
            $index = 0;

            foreach ($cellIterator as $cell) {
                $header = $headers[$index] ?? '';

                if ($header !== '') {
                    $assoc[$header] = $cell?->getCalculatedValue();
                }

                $index++;
            }

            $batch->push(collect($assoc));

            if ($batch->count() >= $batchSize) {
                $import->collection($batch);
                $batch = collect();
            }
        }

        if ($batch->isNotEmpty()) {
            $import->collection($batch);
        }

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
    }

    protected function extractHeaders(Worksheet $sheet): array
    {
        $rowIterator = $sheet->getRowIterator(1);
        $headerRow = $rowIterator->current();

        if (! $headerRow) {
            return [];
        }

        $headers = [];
        $cellIterator = $headerRow->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);

        foreach ($cellIterator as $cell) {
            $headers[] = $this->normalizeHeading($cell?->getValue());
        }

        return $headers;
    }

    protected function normalizeHeading(mixed $value): string
    {
        $heading = trim((string) $value);

        if ($heading === '') {
            return '';
        }

        $heading = mb_strtolower($heading, 'UTF-8');
        $heading = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $heading) ?: $heading;
        $heading = str_replace(['º', '°', 'ª'], '', $heading);
        $heading = preg_replace('/[^a-z0-9]+/', '_', $heading);

        return trim((string) $heading, '_');
    }
}
