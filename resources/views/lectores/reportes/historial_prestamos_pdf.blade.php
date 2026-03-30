<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Historial de préstamos</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111827; }
        h1 { margin: 0 0 6px; font-size: 18px; }
        .meta { margin-bottom: 12px; color: #4b5563; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 6px; vertical-align: top; }
        th { background: #e2e8f0; text-align: left; font-size: 9px; }
        td { font-size: 9px; }
    </style>
</head>
<body>
    <h1>Historial de préstamos</h1>
    <div class="meta">
        <div>Generado: {{ $generadoEn->format('d/m/Y H:i') }}</div>
        <div>Filtros: {{ $filtrosTexto }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Libro</th>
                <th>Biblioteca</th>
                <th>Lector</th>
                <th>Ejemplar</th>
                <th>Tipo</th>
                <th>Estado</th>
                <th>Prestamo</th>
                <th>Fecha préstamo</th>
                <th>Fecha devolución</th>
                <th>Dias</th>
                <th>Registrado por</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($registros as $registro)
                <tr>
                    <td>{{ $registro->id }}</td>
                    <td>{{ $registro->titulo ?: '-' }}</td>
                    <td>{{ $registro->biblioteca_nombre ?: '-' }}</td>
                    <td>{{ $registro->lector_nombre ?: '-' }}</td>
                    <td>{{ $registro->codigo_ejemplar }}</td>
                    <td>{{ $registro->tipo_prestamo_texto }}</td>
                    <td>{{ $registro->estado_general_texto }}</td>
                    <td>{{ $registro->estado_prestamo_texto }}</td>
                    <td>{{ $registro->fecha_prestamo_texto }}</td>
                    <td>{{ $registro->fecha_devolucion_texto }}</td>
                    <td>{{ $registro->dias_prestado ?: '-' }}</td>
                    <td>{{ $registro->bibliotecario_nombre ?: '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
