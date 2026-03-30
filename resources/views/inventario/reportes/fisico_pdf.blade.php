<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de inventario fisico</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #0f172a;
            margin: 24px;
        }
        .report-header {
            margin-bottom: 20px;
        }
        .report-header h1 {
            margin: 0 0 8px;
            font-size: 24px;
        }
        .report-header p {
            margin: 4px 0;
            color: #475569;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            text-align: left;
            vertical-align: top;
            font-size: 12px;
        }
        th {
            background: #f1f5f9;
            font-weight: 700;
        }
        .muted {
            color: #64748b;
        }
        @media print {
            body {
                margin: 10mm;
            }
        }
    </style>
</head>
<body>
    <div class="report-header">
        <h1>Reporte de inventario fisico</h1>
        <p><strong>Biblioteca:</strong> {{ $biblioteca }}</p>
        <p><strong>Generado:</strong> {{ $generadoEn->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>N°</th>
                <th>Materia</th>
                <th>MAT.</th>
                <th>CORR.</th>
                <th>Codigo</th>
                <th>Codigo de programa</th>
                <th>Titulo</th>
                <th>Autores</th>
                <th>Años de publicación</th>
                <th>Número de ejemplares</th>
                <th>Idioma</th>
                <th>Edición</th>
                <th>ISBN</th>
                <th>País</th>
                <th>Pagina</th>
                <th>Editorial</th>
                <th>Fecha</th>
                <th>OBS</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($registros as $index => $registro)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $registro->materias ?: '-' }}</td>
                    <td>{{ $registro->codigo_dewey ?: '-' }}</td>
                    <td>{{ (int) $registro->total_ejemplares }}</td>
                    <td>{{ $registro->codigo ?: '-' }}</td>
                    <td>{{ $registro->codigo_ant ?: '-' }}</td>
                    <td>{{ $registro->titulo ?: '-' }}</td>
                    <td>{{ $registro->autores ?: '-' }}</td>
                    <td>{{ $registro->anio_edicion ?: '-' }}</td>
                    <td>{{ (int) $registro->total_ejemplares }}</td>
                    <td>{{ $registro->idioma_nombre ?: '-' }}</td>
                    <td>{{ $registro->edicion ?: '-' }}</td>
                    <td>{{ $registro->isbn ?: '-' }}</td>
                    <td>{{ $registro->lugar_publicacion ?: '-' }}</td>
                    <td>{{ $registro->paginas ?: '-' }}</td>
                    <td>{{ $registro->editorial_nombre ?: '-' }}</td>
                    <td>{{ $registro->fecha_publicacion ?: ($registro->anio_edicion ?: '-') }}</td>
                    <td>{{ $registro->anotaciones ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="18" class="muted">Sin registros para exportar.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
