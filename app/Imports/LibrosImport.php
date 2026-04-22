<?php

namespace App\Imports;

use App\Models\Autor;
use App\Models\Editorial;
use App\Models\Ejemplar;
use App\Models\Idioma;
use App\Models\Libro;
use App\Models\Materia;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class LibrosImport implements ToCollection, WithHeadingRow
{
    protected int $previewLimit = 300;

    public function __construct(
        protected int $bibliotecaId,
        protected int $tipoRegistroId
    ) {
    }

    protected array $summary = [
        'procesados' => 0,
        'libros_insertados' => 0,
        'omitidos' => 0,
        'errores' => 0,
        'autores_insertados' => 0,
        'materias_insertadas' => 0,
        'ejemplares_creados' => 0,
    ];

    protected array $errors = [];
    protected array $insertedBooks = [];
    protected array $omittedBooks = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;
            $rowData = $row instanceof Collection ? $row->toArray() : (array) $row;
            $data = $this->normalizeRow($rowData);

            if ($this->isEmptyRow($data)) {
                continue;
            }

            $this->summary['procesados']++;

            try {
                DB::transaction(function () use ($data) {
                    $titulo = $this->cleanString($data['titulo']);

                    if ($titulo === null) {
                        throw new \RuntimeException('El titulo es obligatorio.');
                    }

                    $codigoAnt = $this->cleanString($data['codigo_ant']);
                    $isbn = $this->cleanString($data['isbn']);
                    $codMateria = $this->cleanString($data['cod_materia']);
                    $edicion = $this->cleanString($data['edicion']);
                    $cantidadEjemplares = $this->resolveCantidadEjemplares($data['cantidad_ejemplares']);
                    $authorTokens = $this->normalizeAuthorTokens($data['autores']);

                    $libroExistente = $this->findExistingBook($codigoAnt, $isbn, $titulo, $authorTokens, $edicion);

                    if ($libroExistente) {
                        $this->summary['omitidos']++;
                        $this->createEjemplares($libroExistente, $cantidadEjemplares);
                        $this->pushOmittedBook([
                            'titulo' => $libroExistente->titulo,
                            'codigo_ant' => $libroExistente->codigo_ant,
                            'cod_materia' => $libroExistente->cod_materia,
                            'numero' => $libroExistente->numero,
                            'ejemplares' => $cantidadEjemplares,
                            'motivo' => 'Libro existente; se agregaron ejemplares.',
                        ]);
                        return;
                    }

                    $idiomaId = $this->resolveIdiomaId($data['idioma']);
                    $editorialId = $this->resolveEditorialId($data['editorial']);
                    $numero = Libro::siguienteNumeroParaMateria($codMateria);

                    $libro = Libro::create([
                        'cod_materia' => $codMateria,
                        'numero' => $numero,
                        'codigo_ant' => $codigoAnt,
                        'titulo' => $titulo,
                        'anio_edicion' => $this->cleanInteger($data['anio_edicion']),
                        'idioma' => $idiomaId,
                        'edicion' => $edicion,
                        'isbn' => $isbn,
                        'lugar_publicacion' => $this->cleanString($data['lugar_publicacion']),
                        'paginas' => $this->cleanInteger($data['paginas']),
                        'editorial_id' => $editorialId,
                        'tipo_registro_id' => $this->tipoRegistroId,
                        'estado' => 1,
                    ]);

                    $this->summary['libros_insertados']++;
                    $this->pushInsertedBook([
                        'titulo' => $libro->titulo,
                        'codigo_ant' => $libro->codigo_ant,
                        'cod_materia' => $libro->cod_materia,
                        'numero' => $libro->numero,
                        'ejemplares' => $cantidadEjemplares,
                    ]);
                    $this->attachAutores($libro, $data['autores']);
                    $this->attachMaterias($libro, $data['materias']);
                    $this->createEjemplares($libro, $cantidadEjemplares);
                });
            } catch (\Throwable $e) {
                $this->summary['errores']++;
                $this->errors[] = 'Fila ' . $rowNumber . ': ' . $e->getMessage();
            }
        }
    }

    public function getSummary(): array
    {
        return $this->summary;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getInsertedBooks(): array
    {
        return $this->insertedBooks;
    }

    public function getOmittedBooks(): array
    {
        return $this->omittedBooks;
    }

    protected function pushInsertedBook(array $book): void
    {
        if (count($this->insertedBooks) < $this->previewLimit) {
            $this->insertedBooks[] = $book;
        }
    }

    protected function pushOmittedBook(array $book): void
    {
        if (count($this->omittedBooks) < $this->previewLimit) {
            $this->omittedBooks[] = $book;
        }
    }

    protected function normalizeRow(array $row): array
    {
        return [
            'cod_materia' => $this->valueFromRow($row, ['mat', 'cod_materia']),
            'codigo_ant' => $this->valueFromRow($row, ['codigo', 'codigo_ant']),
            'titulo' => $this->valueFromRow($row, ['titulo']),
            'anio_edicion' => $this->valueFromRow($row, ['ano', 'a_no', 'anio', 'anio_edicion']),
            'idioma' => $this->valueFromRow($row, ['idioma']),
            'edicion' => $this->valueFromRow($row, ['edicion']),
            'isbn' => $this->valueFromRow($row, ['isbn']),
            'lugar_publicacion' => $this->valueFromRow($row, ['pais', 'lugar_publicacion']),
            'paginas' => $this->valueFromRow($row, ['paginas']),
            'editorial' => $this->valueFromRow($row, ['editorial']),
            'cantidad_ejemplares' => $this->valueFromRow($row, ['ejemplar', 'ejemplares', 'cantidad_ejemplares']),
            'autores' => $this->valueFromRow($row, ['autores', 'autor']),
            'materias' => $this->valueFromRow($row, ['materias', 'materia']),
        ];
    }

    protected function valueFromRow(array $row, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row)) {
                return $row[$key];
            }
        }

        return null;
    }

    protected function isEmptyRow(array $data): bool
    {
        foreach ($data as $value) {
            if ($this->cleanString($value) !== null) {
                return false;
            }
        }

        return true;
    }

    protected function findExistingBook(?string $codigoAnt, ?string $isbn, ?string $titulo, array $authorTokens = [], ?string $edicion = null): ?Libro
    {
        if ($codigoAnt !== null) {
            $book = Libro::query()
                ->with('autores')
                ->whereRaw('LOWER(TRIM(codigo_ant)) = ?', [mb_strtolower($codigoAnt)])
                ->first();

            if ($book && $this->authorsMatch($book, $authorTokens) && $this->editionMatches($book, $edicion)) {
                return $book;
            }
        }

        if ($isbn !== null) {
            $book = Libro::query()
                ->with('autores')
                ->whereRaw('LOWER(TRIM(isbn)) = ?', [mb_strtolower($isbn)])
                ->first();

            if ($book && $this->authorsMatch($book, $authorTokens) && $this->editionMatches($book, $edicion)) {
                return $book;
            }
        }

        if ($titulo !== null) {
            return Libro::query()
                ->with('autores')
                ->whereRaw('LOWER(TRIM(titulo)) = ?', [mb_strtolower($titulo)])
                ->get()
                ->first(fn (Libro $book) => $this->authorsMatch($book, $authorTokens) && $this->editionMatches($book, $edicion));
        }

        return null;
    }

    protected function editionMatches(Libro $book, ?string $edicion): bool
    {
        return $this->normalizeEdition($book->edicion) === $this->normalizeEdition($edicion);
    }

    protected function normalizeEdition(?string $value): ?string
    {
        $text = $this->cleanString($value);

        if ($text === null) {
            return null;
        }

        $text = mb_strtolower($text, 'UTF-8');
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
        $text = preg_replace('/[^a-z0-9]+/', ' ', $text);
        $text = trim((string) $text);

        return $text === '' ? null : $text;
    }

    protected function authorsMatch(Libro $book, array $authorTokens): bool
    {
        if ($authorTokens === []) {
            return true;
        }

        $bookTokens = $book->autores
            ->flatMap(function (Autor $autor) {
                return array_filter([
                    $this->normalizeAuthorText(trim((string) ($autor->apellidos ?? '')) . ';' . trim((string) ($autor->nombres ?? ''))),
                    $this->normalizeAuthorText(trim((string) ($autor->nombres ?? '')) . ';' . trim((string) ($autor->apellidos ?? ''))),
                    $this->normalizeAuthorText(trim((string) ($autor->apellidos ?? '')) . ' ' . trim((string) ($autor->nombres ?? ''))),
                    $this->normalizeAuthorText(trim((string) ($autor->nombres ?? '')) . ' ' . trim((string) ($autor->apellidos ?? ''))),
                ]);
            })
            ->unique()
            ->values()
            ->all();

        sort($bookTokens);
        $rowTokens = $authorTokens;
        sort($rowTokens);

        return $bookTokens === $rowTokens;
    }

    protected function normalizeAuthorTokens(mixed $value): array
    {
        return collect($this->splitMultiValue($value))
            ->map(fn ($author) => $this->normalizeAuthorText($author))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    protected function normalizeAuthorText(?string $value): ?string
    {
        $text = $this->cleanString($value);

        if ($text === null) {
            return null;
        }

        $text = mb_strtolower($text, 'UTF-8');
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
        $text = preg_replace('/[^a-z0-9]+/', ' ', $text);
        $text = trim((string) $text);

        return $text === '' ? null : $text;
    }

    protected function resolveIdiomaId(mixed $value): ?int
    {
        $nombre = $this->cleanString($value);

        if ($nombre === null) {
            return null;
        }

        return Idioma::query()
            ->whereRaw('LOWER(TRIM(nombre)) = ?', [mb_strtolower($nombre)])
            ->value('id');
    }

    protected function resolveEditorialId(mixed $value): ?int
    {
        $nombre = $this->cleanString($value);

        if ($nombre === null) {
            return null;
        }

        $editorial = Editorial::query()
            ->whereRaw('LOWER(TRIM(nombre)) = ?', [mb_strtolower($nombre)])
            ->first();

        if ($editorial) {
            return $editorial->id;
        }

        return Editorial::create([
            'nombre' => $nombre,
            'estado' => 1,
        ])->id;
    }

    protected function attachAutores(Libro $libro, mixed $value): void
    {
        foreach ($this->splitMultiValue($value) as $autorNombre) {
            $autor = Autor::query()
                ->whereRaw("LOWER(TRIM(CONCAT(apellidos, ' ', nombres))) = ?", [mb_strtolower($autorNombre)])
                ->orWhereRaw("LOWER(TRIM(CONCAT(nombres, ' ', apellidos))) = ?", [mb_strtolower($autorNombre)])
                ->first();

            if (! $autor) {
                continue;
            }

            $changes = $libro->autores()->syncWithoutDetaching([$autor->id]);
            $this->summary['autores_insertados'] += count($changes['attached'] ?? []);
        }
    }

    protected function attachMaterias(Libro $libro, mixed $value): void
    {
        foreach ($this->splitMultiValue($value) as $materiaNombre) {
            $materia = Materia::query()
                ->whereRaw('LOWER(TRIM(nombre)) = ?', [mb_strtolower($materiaNombre)])
                ->first();

            if (! $materia) {
                continue;
            }

            $changes = $libro->materias()->syncWithoutDetaching([$materia->id]);
            $this->summary['materias_insertadas'] += count($changes['attached'] ?? []);
        }
    }

    protected function createEjemplares(Libro $libro, int $cantidadEjemplares): void
    {
        $inicio = Ejemplar::siguienteCodigoInternoParaLibro($libro->id);

        for ($i = 0; $i < $cantidadEjemplares; $i++) {
            $codigoInterno = $inicio + $i;

            Ejemplar::crearDesdeImportacion($libro, $this->bibliotecaId, $codigoInterno);
            $this->summary['ejemplares_creados']++;
        }
    }

    protected function splitMultiValue(mixed $value): array
    {
        $text = $this->cleanString($value);

        if ($text === null) {
            return [];
        }

        $items = preg_split('/[,;|]+/', $text) ?: [];
        $items = array_map(fn ($item) => $this->cleanString($item), $items);
        $items = array_filter($items);

        return array_values(array_unique($items));
    }

    protected function cleanString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = trim((string) $value);
        $text = preg_replace('/\s+/u', ' ', $text);

        return $text === '' ? null : $text;
    }

    protected function cleanInteger(mixed $value): ?int
    {
        $text = $this->cleanString($value);

        if ($text === null) {
            return null;
        }

        $digits = preg_replace('/[^\d-]/', '', $text);

        return $digits === '' || ! is_numeric($digits) ? null : (int) $digits;
    }

    protected function resolveCantidadEjemplares(mixed $value): int
    {
        $cantidadEjemplares = (int) ($this->cleanInteger($value) ?? 1);

        return $cantidadEjemplares > 0 ? $cantidadEjemplares : 1;
    }
}
