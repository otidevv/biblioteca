<?php

namespace App\Services;

use App\Models\Carrera;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use SimpleXMLElement;
use ZipArchive;

class LectorImportService
{
    private const STORAGE_DIR = 'importaciones_lectores';

    public function templateColumns(): array
    {
        return [
            ['campo' => 'tipo_persona', 'required' => 'Si', 'detalle' => 'ESTUDIANTE, DOCENTE, ADMINISTRATIVO o EXTERNO'],
            ['campo' => 'dni', 'required' => 'Si', 'detalle' => 'Documento unico del lector'],
            ['campo' => 'nombres', 'required' => 'Si', 'detalle' => 'Nombres completos'],
            ['campo' => 'apellido_paterno', 'required' => 'Si', 'detalle' => 'Apellido paterno'],
            ['campo' => 'apellido_materno', 'required' => 'No', 'detalle' => 'Apellido materno'],
            ['campo' => 'sexo', 'required' => 'Si', 'detalle' => 'M, F u O'],
            ['campo' => 'telefono', 'required' => 'Si', 'detalle' => 'Telefono de contacto'],
            ['campo' => 'email_personal', 'required' => 'Si', 'detalle' => 'Correo que sera tambien el usuario de acceso'],
            ['campo' => 'direccion', 'required' => 'No', 'detalle' => 'Direccion del lector'],
            ['campo' => 'codigo_institucional', 'required' => 'Solo estudiantes', 'detalle' => 'Codigo institucional unico'],
            ['campo' => 'carrera', 'required' => 'Solo estudiantes', 'detalle' => 'ID o nombre exacto de la carrera'],
            ['campo' => 'estado_academico', 'required' => 'Solo estudiantes', 'detalle' => '1, 2, ESTUDIANTE o EGRESADO'],
            ['campo' => 'password', 'required' => 'Si', 'detalle' => 'Contrasena inicial, minimo 6 caracteres'],
        ];
    }

    public function preview(UploadedFile $file): array
    {
        $rows = $this->parseSpreadsheet($file);

        if (count($rows) < 2) {
            throw new InvalidArgumentException('El archivo no contiene filas para importar.');
        }

        [$headers, $dataRows] = $this->extractRows($rows);
        $previewRows = $this->validateRows($dataRows);
        $summary = $this->buildSummary($previewRows);

        $token = (string) Str::uuid();
        Storage::disk('local')->put($this->tokenPath($token), json_encode([
            'headers' => $headers,
            'rows' => $previewRows,
            'summary' => $summary,
            'file_name' => $file->getClientOriginalName(),
        ], JSON_UNESCAPED_UNICODE));

        return [
            'token' => $token,
            'headers' => $headers,
            'rows' => $previewRows,
            'summary' => $summary,
            'can_import' => $summary['total'] > 0 && $summary['invalidos'] === 0,
        ];
    }

    public function reviewTokenRows(string $token, array $editedRows): array
    {
        $payload = $this->readToken($token);
        $previewRows = $this->validateRows($this->normalizeEditableRows($editedRows, $payload['rows'] ?? []));
        $summary = $this->buildSummary($previewRows);

        Storage::disk('local')->put($this->tokenPath($token), json_encode([
            'headers' => $payload['headers'] ?? [],
            'rows' => $previewRows,
            'summary' => $summary,
            'file_name' => $payload['file_name'] ?? null,
        ], JSON_UNESCAPED_UNICODE));

        return [
            'token' => $token,
            'headers' => $payload['headers'] ?? [],
            'rows' => $previewRows,
            'summary' => $summary,
            'can_import' => $summary['total'] > 0 && $summary['invalidos'] === 0,
        ];
    }

    public function import(string $token, ?array $rows = null): array
    {
        $this->readToken($token);
        $rows = $rows ?? ((array) ($this->readToken($token)['rows'] ?? []));
        $summary = $this->buildSummary($rows);

        if (empty($rows)) {
            throw new RuntimeException('No hay datos listos para importar.');
        }

        if (($summary['invalidos'] ?? 0) > 0) {
            throw new RuntimeException('Corrige las filas observadas antes de ejecutar la carga masiva.');
        }

        $created = DB::transaction(function () use ($rows) {
            $total = 0;

            foreach ($rows as $row) {
                $data = $row['data'];

                if (Persona::where('dni', $data['dni'])->exists()) {
                    throw new RuntimeException("El DNI {$data['dni']} ya existe. Vuelve a previsualizar el archivo.");
                }

                if (User::where('email', $data['email_personal'])->exists()) {
                    throw new RuntimeException("El correo {$data['email_personal']} ya existe. Vuelve a previsualizar el archivo.");
                }

                if ($data['tipo_persona'] === 'ESTUDIANTE' && Persona::where('codigo_institucional', $data['codigo_institucional'])->exists()) {
                    throw new RuntimeException("El codigo institucional {$data['codigo_institucional']} ya existe. Vuelve a previsualizar el archivo.");
                }

                $persona = Persona::create([
                    'dni' => $data['dni'],
                    'tipo_persona' => $data['tipo_persona'],
                    'nombres' => $data['nombres'],
                    'apellido_paterno' => $data['apellido_paterno'],
                    'apellido_materno' => $data['apellido_materno'] !== '' ? $data['apellido_materno'] : null,
                    'sexo' => $data['sexo'],
                    'telefono' => $data['telefono'],
                    'email_personal' => $data['email_personal'],
                    'direccion' => $data['direccion'] !== '' ? $data['direccion'] : null,
                    'codigo_institucional' => $data['codigo_institucional'] !== '' ? $data['codigo_institucional'] : null,
                    'carrera_id' => $data['carrera_id'],
                    'estado_academico' => $data['estado_academico'] !== '' ? $data['estado_academico'] : null,
                ]);

                $user = User::create([
                    'name' => trim(implode(' ', array_filter([
                        $data['nombres'],
                        $data['apellido_paterno'],
                        $data['apellido_materno'],
                    ]))),
                    'email' => $data['email_personal'],
                    'password' => Hash::make($data['password']),
                    'estado' => 1,
                    'origen' => 'local',
                    'tipo_usuario' => 'Lector',
                    'persona_id' => $persona->id,
                ]);

                $user->roles()->sync([5]);
                $total++;
            }

            return $total;
        });

        Storage::disk('local')->delete($this->tokenPath($token));

        return ['created' => $created];
    }

    public function templateCsv(): string
    {
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, chr(239) . chr(187) . chr(191));

        foreach ($this->templateRows() as $row) {
            fputcsv($stream, $row);
        }

        rewind($stream);

        return stream_get_contents($stream) ?: '';
    }

    public function templateXlsx(): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'lectores_xlsx_');

        if ($tempFile === false) {
            throw new RuntimeException('No se pudo generar el archivo temporal de la plantilla.');
        }

        $zip = new ZipArchive();

        if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            @unlink($tempFile);
            throw new RuntimeException('No se pudo construir la plantilla XLSX.');
        }

        $zip->addFromString('[Content_Types].xml', $this->contentTypesXml());
        $zip->addFromString('_rels/.rels', $this->rootRelationsXml());
        $zip->addFromString('xl/workbook.xml', $this->workbookXml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelationsXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->sheetXml($this->templateRows()));
        $zip->close();

        $binary = file_get_contents($tempFile);
        @unlink($tempFile);

        if ($binary === false) {
            throw new RuntimeException('No se pudo leer la plantilla XLSX generada.');
        }

        return $binary;
    }

    private function templateRows(): array
    {
        return [
            array_column($this->templateColumns(), 'campo'),
            ['ESTUDIANTE', '70000001', 'ANA MARIA', 'QUISPE', 'HUAMAN', 'F', '987654321', 'ana.quispe@correo.com', 'AV. LOS LIBROS 123', '202410001', 'INGENIERIA DE SISTEMAS E INFORMATICA', '1', 'Clave123'],
            ['DOCENTE', '70000002', 'LUIS ALBERTO', 'ROJAS', 'PAREDES', 'M', '912345678', 'luis.rojas@correo.com', 'JR. UNIVERSIDAD 456', '', '', '', 'Clave123'],
        ];
    }

    private function contentTypesXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
    <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
</Types>
XML;
    }

    private function rootRelationsXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>
XML;
    }

    private function workbookXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <sheets>
        <sheet name="Lectores" sheetId="1" r:id="rId1"/>
    </sheets>
</workbook>
XML;
    }

    private function workbookRelationsXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
</Relationships>
XML;
    }

    private function sheetXml(array $rows): string
    {
        $xmlRows = [];

        foreach ($rows as $rowIndex => $row) {
            $cells = [];

            foreach ($row as $columnIndex => $value) {
                $cellRef = $this->columnName($columnIndex) . ($rowIndex + 1);
                $cells[] = '<c r="' . $cellRef . '" t="inlineStr"><is><t>' . $this->escapeXml((string) $value) . '</t></is></c>';
            }

            $xmlRows[] = '<row r="' . ($rowIndex + 1) . '">' . implode('', $cells) . '</row>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<sheetData>' . implode('', $xmlRows) . '</sheetData>'
            . '</worksheet>';
    }

    private function columnName(int $index): string
    {
        $name = '';
        $index++;

        while ($index > 0) {
            $index--;
            $name = chr(65 + ($index % 26)) . $name;
            $index = intdiv($index, 26);
        }

        return $name;
    }

    private function escapeXml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function readToken(string $token): array
    {
        if (!Storage::disk('local')->exists($this->tokenPath($token))) {
            throw new RuntimeException('La previsualizacion ya no esta disponible. Sube el archivo nuevamente.');
        }

        return json_decode(Storage::disk('local')->get($this->tokenPath($token)), true, 512, JSON_THROW_ON_ERROR);
    }

    private function tokenPath(string $token): string
    {
        return self::STORAGE_DIR . '/' . $token . '.json';
    }

    private function parseSpreadsheet(UploadedFile $file): array
    {
        $extension = strtolower((string) $file->getClientOriginalExtension());

        return match ($extension) {
            'csv', 'txt' => $this->parseCsv($file->getRealPath()),
            'xlsx' => $this->parseXlsx($file->getRealPath()),
            default => throw new InvalidArgumentException('Solo se permiten archivos .xlsx o .csv.'),
        };
    }

    private function parseCsv(string $path): array
    {
        $rows = [];
        $handle = fopen($path, 'r');

        if (!$handle) {
            throw new RuntimeException('No se pudo leer el archivo CSV.');
        }

        $firstLine = fgets($handle);
        rewind($handle);
        $delimiter = substr_count((string) $firstLine, ';') > substr_count((string) $firstLine, ',') ? ';' : ',';

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rows[] = array_map(fn($value) => trim((string) $value), $row);
        }

        fclose($handle);

        return $rows;
    }

    private function parseXlsx(string $path): array
    {
        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            throw new RuntimeException('No se pudo abrir el archivo Excel.');
        }

        $sharedStrings = $this->readSharedStrings($zip);
        $sheetPath = $this->detectFirstSheetPath($zip);
        $worksheetXml = simplexml_load_string((string) $zip->getFromName($sheetPath));

        if (!$worksheetXml instanceof SimpleXMLElement) {
            throw new RuntimeException('No se pudo leer la hoja principal del Excel.');
        }

        $rows = [];

        foreach ($worksheetXml->sheetData->row as $row) {
            $current = [];

            foreach ($row->c as $cell) {
                $column = preg_replace('/\d+/', '', (string) $cell['r']);
                $current[$this->columnToIndex($column)] = $this->readCellValue($cell, $sharedStrings);
            }

            if ($current !== []) {
                ksort($current);
                $rows[] = array_values($current);
            }
        }

        $zip->close();

        return $rows;
    }

    private function readSharedStrings(ZipArchive $zip): array
    {
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');

        if ($sharedXml === false) {
            return [];
        }

        $xml = simplexml_load_string($sharedXml);
        $values = [];

        foreach ($xml->si as $item) {
            if (isset($item->t)) {
                $values[] = (string) $item->t;
                continue;
            }

            $text = '';
            foreach ($item->r as $run) {
                $text .= (string) $run->t;
            }
            $values[] = $text;
        }

        return $values;
    }

    private function detectFirstSheetPath(ZipArchive $zip): string
    {
        $workbookXml = simplexml_load_string((string) $zip->getFromName('xl/workbook.xml'));
        $relsXml = simplexml_load_string((string) $zip->getFromName('xl/_rels/workbook.xml.rels'));
        $workbookNs = $workbookXml?->getNamespaces(true) ?? [];
        $firstSheet = $workbookXml?->sheets?->sheet[0];
        $relationId = (string) $firstSheet?->attributes($workbookNs['r'] ?? '')?->id;

        foreach ($relsXml?->Relationship ?? [] as $relation) {
            if ((string) $relation['Id'] === $relationId) {
                return 'xl/' . ltrim((string) $relation['Target'], '/');
            }
        }

        return 'xl/worksheets/sheet1.xml';
    }

    private function readCellValue(SimpleXMLElement $cell, array $sharedStrings): string
    {
        return match ((string) $cell['t']) {
            's' => trim((string) ($sharedStrings[(int) $cell->v] ?? '')),
            'inlineStr' => trim((string) $cell->is->t),
            default => trim((string) ($cell->v ?? '')),
        };
    }

    private function columnToIndex(string $column): int
    {
        $index = 0;
        foreach (str_split(strtoupper($column)) as $letter) {
            $index = ($index * 26) + (ord($letter) - 64);
        }

        return max(0, $index - 1);
    }

    private function extractRows(array $rows): array
    {
        $headers = $this->normalizeHeaders(array_shift($rows) ?? []);
        $required = ['tipo_persona', 'dni', 'nombres', 'apellido_paterno', 'sexo', 'telefono', 'email_personal', 'password'];
        $missing = array_diff($required, $headers);

        if ($missing !== []) {
            throw new InvalidArgumentException('Faltan columnas obligatorias en la plantilla: ' . implode(', ', $missing));
        }

        $dataRows = [];

        foreach ($rows as $index => $row) {
            $assoc = [];
            foreach ($headers as $position => $header) {
                $assoc[$header] = trim((string) ($row[$position] ?? ''));
            }

            if (collect($assoc)->filter(fn($value) => $value !== '')->isEmpty()) {
                continue;
            }

            $dataRows[] = ['excel_row' => $index + 2, 'values' => $assoc];
        }

        return [$headers, $dataRows];
    }

    private function normalizeHeaders(array $headers): array
    {
        $aliases = [
            'tipo persona' => 'tipo_persona',
            'tipo_persona' => 'tipo_persona',
            'dni' => 'dni',
            'documento' => 'dni',
            'nombres' => 'nombres',
            'apellido paterno' => 'apellido_paterno',
            'apellido_paterno' => 'apellido_paterno',
            'apellido materno' => 'apellido_materno',
            'apellido_materno' => 'apellido_materno',
            'sexo' => 'sexo',
            'telefono' => 'telefono',
            'celular' => 'telefono',
            'email personal' => 'email_personal',
            'email_personal' => 'email_personal',
            'correo personal' => 'email_personal',
            'correo' => 'email_personal',
            'direccion' => 'direccion',
            'codigo institucional' => 'codigo_institucional',
            'codigo_institucional' => 'codigo_institucional',
            'carrera' => 'carrera',
            'estado academico' => 'estado_academico',
            'estado_academico' => 'estado_academico',
            'password' => 'password',
            'contrasena' => 'password',
        ];

        return array_map(function ($header) use ($aliases) {
            $normalized = Str::of((string) $header)->lower()->ascii()->replace(['-', '/'], ' ')->squish()->value();
            return $aliases[$normalized] ?? $normalized;
        }, $headers);
    }

    private function buildSummary(array $previewRows): array
    {
        return [
            'total' => count($previewRows),
            'validos' => collect($previewRows)->where('is_valid', true)->count(),
            'invalidos' => collect($previewRows)->where('is_valid', false)->count(),
            'estudiantes' => collect($previewRows)->where('data.tipo_persona', 'ESTUDIANTE')->count(),
        ];
    }

    private function normalizeEditableRows(array $editedRows, array $baseRows): array
    {
        $templateFields = array_column($this->templateColumns(), 'campo');
        $baseByExcelRow = collect($baseRows)->keyBy('excel_row');

        return collect($editedRows)->values()->map(function ($row, $index) use ($templateFields, $baseByExcelRow) {
            $excelRow = (int) ($row['excel_row'] ?? 0);
            $baseRow = $baseByExcelRow->get($excelRow, []);
            $baseData = $baseRow['data'] ?? [];
            $values = [];

            foreach ($templateFields as $field) {
                $values[$field] = trim((string) ($row[$field] ?? $row['data'][$field] ?? $baseData[$field] ?? ''));
            }

            return [
                'excel_row' => $excelRow > 0 ? $excelRow : ((int) ($baseRow['excel_row'] ?? ($index + 2))),
                'values' => $values,
            ];
        })->all();
    }

    private function validateRows(array $rows): array
    {
        $carreras = Carrera::query()->get(['id', 'nombre']);
        $careerById = $carreras->keyBy(fn($item) => (string) $item->id);
        $careerByName = $carreras->keyBy(fn($item) => Str::lower(Str::ascii($item->nombre)));
        $dniCounts = array_count_values(array_map(fn($row) => $row['values']['dni'] ?? '', $rows));
        $emailCounts = array_count_values(array_map(fn($row) => Str::lower($row['values']['email_personal'] ?? ''), $rows));
        $codigoCounts = array_count_values(array_map(fn($row) => $row['values']['codigo_institucional'] ?? '', $rows));
        $existingDni = Persona::whereIn('dni', array_keys($dniCounts))->pluck('dni')->flip()->all();
        $existingEmail = User::whereIn('email', array_keys($emailCounts))->pluck('email')->map(fn($email) => Str::lower($email))->flip()->all();
        $existingCodigo = Persona::whereIn('codigo_institucional', array_filter(array_keys($codigoCounts)))->pluck('codigo_institucional')->flip()->all();

        return array_map(function ($row) use ($careerById, $careerByName, $dniCounts, $emailCounts, $codigoCounts, $existingDni, $existingEmail, $existingCodigo) {
            $data = $row['values'];
            $errors = [];
            $tipoPersona = Str::upper(trim((string) ($data['tipo_persona'] ?? '')));
            $sexo = Str::upper(trim((string) ($data['sexo'] ?? '')));
            $email = Str::lower(trim((string) ($data['email_personal'] ?? '')));
            $codigoInstitucional = trim((string) ($data['codigo_institucional'] ?? ''));
            $password = trim((string) ($data['password'] ?? ''));
            $carreraInput = trim((string) ($data['carrera'] ?? ''));
            $estadoAcademico = Str::upper(trim((string) ($data['estado_academico'] ?? '')));

            if (!in_array($tipoPersona, ['ESTUDIANTE', 'DOCENTE', 'ADMINISTRATIVO', 'EXTERNO'], true)) {
                $errors[] = 'Tipo persona invalido.';
            }
            if (($data['dni'] ?? '') === '') {
                $errors[] = 'El DNI es obligatorio.';
            } elseif (($dniCounts[$data['dni']] ?? 0) > 1) {
                $errors[] = 'El DNI esta repetido dentro del archivo.';
            } elseif (isset($existingDni[$data['dni']])) {
                $errors[] = 'El DNI ya existe en el sistema.';
            }
            if (($data['nombres'] ?? '') === '') {
                $errors[] = 'Los nombres son obligatorios.';
            }
            if (($data['apellido_paterno'] ?? '') === '') {
                $errors[] = 'El apellido paterno es obligatorio.';
            }
            if (!in_array($sexo, ['M', 'F', 'O'], true)) {
                $errors[] = 'El sexo debe ser M, F u O.';
            }
            if (($data['telefono'] ?? '') === '') {
                $errors[] = 'El telefono es obligatorio.';
            }
            if ($email === '') {
                $errors[] = 'El correo personal es obligatorio.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'El correo personal no es valido.';
            } elseif (($emailCounts[$email] ?? 0) > 1) {
                $errors[] = 'El correo esta repetido dentro del archivo.';
            } elseif (isset($existingEmail[$email])) {
                $errors[] = 'El correo ya existe en el sistema.';
            }
            if (Str::length($password) < 6) {
                $errors[] = 'La contrasena debe tener al menos 6 caracteres.';
            }

            $careerId = null;

            if ($tipoPersona === 'ESTUDIANTE') {
                if ($codigoInstitucional === '') {
                    $errors[] = 'El codigo institucional es obligatorio para estudiantes.';
                } elseif (($codigoCounts[$codigoInstitucional] ?? 0) > 1) {
                    $errors[] = 'El codigo institucional esta repetido dentro del archivo.';
                } elseif (isset($existingCodigo[$codigoInstitucional])) {
                    $errors[] = 'El codigo institucional ya existe en el sistema.';
                }
                if ($carreraInput === '') {
                    $errors[] = 'La carrera es obligatoria para estudiantes.';
                } else {
                    $resolvedCareer = $careerById[$carreraInput] ?? $careerByName[Str::lower(Str::ascii($carreraInput))] ?? null;
                    if (!$resolvedCareer) {
                        $errors[] = 'La carrera indicada no existe.';
                    } else {
                        $careerId = $resolvedCareer->id;
                    }
                }
                if (!in_array($estadoAcademico, ['1', '2', 'ESTUDIANTE', 'EGRESADO'], true)) {
                    $errors[] = 'El estado academico debe ser 1, 2, ESTUDIANTE o EGRESADO.';
                }
                $estadoAcademico = in_array($estadoAcademico, ['2', 'EGRESADO'], true) ? '2' : '1';
            } else {
                $codigoInstitucional = '';
                $estadoAcademico = '';
                $carreraInput = '';
            }

            return [
                'excel_row' => $row['excel_row'],
                'is_valid' => $errors === [],
                'errors' => $errors,
                'data' => [
                    'tipo_persona' => $tipoPersona,
                    'dni' => trim((string) ($data['dni'] ?? '')),
                    'nombres' => trim((string) ($data['nombres'] ?? '')),
                    'apellido_paterno' => trim((string) ($data['apellido_paterno'] ?? '')),
                    'apellido_materno' => trim((string) ($data['apellido_materno'] ?? '')),
                    'sexo' => $sexo,
                    'telefono' => trim((string) ($data['telefono'] ?? '')),
                    'email_personal' => $email,
                    'direccion' => trim((string) ($data['direccion'] ?? '')),
                    'codigo_institucional' => $codigoInstitucional,
                    'carrera' => $carreraInput,
                    'carrera_id' => $careerId,
                    'estado_academico' => $estadoAcademico,
                    'password' => $password,
                ],
            ];
        }, $rows);
    }
}
