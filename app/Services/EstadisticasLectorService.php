<?php

namespace App\Services;

use App\Models\ConstanciaLector;
use App\Models\Prestamo;
use App\Models\Sancion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Estadisticas de lectura del estudiante y su nivel de lector.
 *
 * El nivel se calcula sobre los ultimos MESES_PERIODO meses calendario (incluido el actual)
 * y exige constancia: cantidad de prestamos Y meses distintos con actividad, para que una
 * racha puntual no cuente como lectura frecuente.
 */
class EstadisticasLectorService
{
    const MESES_PERIODO = 6;

    // Ordenados de menor a mayor. prestamos/meses: minimos dentro del periodo.
    const NIVELES = [
        'inicial'   => ['nombre' => 'Lector inicial',   'prestamos' => 0,  'meses' => 0],
        'activo'    => ['nombre' => 'Lector activo',    'prestamos' => 3,  'meses' => 1],
        'frecuente' => ['nombre' => 'Lector frecuente', 'prestamos' => 5,  'meses' => 3],
        'destacado' => ['nombre' => 'Lector destacado', 'prestamos' => 10, 'meses' => 4],
    ];

    // Niveles que pueden emitir constancia.
    const NIVELES_CON_CONSTANCIA = ['frecuente', 'destacado'];

    const DIAS_VIGENCIA_CONSTANCIA = 30;

    public function calcular(User $user): array
    {
        $prestamos = Prestamo::with(['ejemplar.libro.materias', 'ejemplar.biblioteca'])
            ->where('lector_id', $user->id)
            ->get();

        $desde = now()->startOfMonth()->subMonths(self::MESES_PERIODO - 1);
        $hasta = now();
        $enPeriodo = $prestamos->filter(fn ($p) => $p->fecha_prestamo && $p->fecha_prestamo->gte($desde));

        $prestamosPeriodo = $enPeriodo->count();
        $mesesActivos = $enPeriodo->groupBy(fn ($p) => $p->fecha_prestamo->format('Y-m'))->count();

        $devueltos = $prestamos->where('estado', 2);
        $aTiempo = $devueltos->filter(fn ($p) => (int) $p->estado_prestamo !== 2)->count();

        [$nivel, $siguiente] = $this->resolverNivel($prestamosPeriodo, $mesesActivos);

        return [
            'periodo' => ['desde' => $desde, 'hasta' => $hasta, 'meses' => self::MESES_PERIODO],
            'total_prestamos' => $prestamos->count(),
            'prestamos_periodo' => $prestamosPeriodo,
            'meses_activos' => $mesesActivos,
            'libros_distintos' => $prestamos->pluck('ejemplar.libro_id')->filter()->unique()->count(),
            'devueltos' => $devueltos->count(),
            'puntualidad' => $devueltos->count() > 0 ? (int) round($aTiempo / $devueltos->count() * 100) : null,
            'en_sala' => $prestamos->where('prestamo_lugar', Prestamo::EN_SALA)->count(),
            'a_casa' => $prestamos->where('prestamo_lugar', Prestamo::A_CASA)->count(),
            'bibliotecas' => $prestamos->pluck('ejemplar.biblioteca.nombre')->filter()->unique()->values()->all(),
            'materias' => $this->materiasPrincipales($prestamos),
            'serie_mensual' => $this->serieMensual($enPeriodo, $desde),
            'miembro_desde' => $prestamos->min('fecha_prestamo') ?? $user->created_at,
            'ultimo_prestamo' => $prestamos->max('fecha_prestamo'),
            'nivel' => $nivel,
            'siguiente' => $siguiente,
            'puede_constancia' => in_array($nivel['clave'], self::NIVELES_CON_CONSTANCIA, true),
        ];
    }

    /**
     * Emite (o reutiliza la del dia) la constancia verificable. Devuelve null si el nivel no alcanza.
     */
    public function emitirConstancia(User $user): ?ConstanciaLector
    {
        $estadisticas = $this->calcular($user);

        if (! $estadisticas['puede_constancia']) {
            return null;
        }

        $deHoy = ConstanciaLector::where('user_id', $user->id)
            ->where('nivel', $estadisticas['nivel']['clave'])
            ->whereDate('emitida_at', today())
            ->latest('id')
            ->first();

        if ($deHoy) {
            return $deHoy;
        }

        $persona = $user->persona()->with('carrera')->first();

        return ConstanciaLector::create([
            'codigo' => $this->nuevoCodigo(),
            'user_id' => $user->id,
            'nivel' => $estadisticas['nivel']['clave'],
            'emitida_at' => now(),
            'vence_at' => now()->addDays(self::DIAS_VIGENCIA_CONSTANCIA),
            'datos' => [
                'nombre' => $persona
                    ? trim($persona->apellido_paterno . ' ' . $persona->apellido_materno) . ', ' . $persona->nombres
                    : $user->name,
                'dni' => $persona?->dni,
                'codigo_estudiante' => $persona?->codigo_institucional,
                'carrera' => $persona?->carrera?->nombre,
                'nivel_nombre' => $estadisticas['nivel']['nombre'],
                'periodo_desde' => $estadisticas['periodo']['desde']->toDateString(),
                'periodo_hasta' => $estadisticas['periodo']['hasta']->toDateString(),
                'meses_periodo' => self::MESES_PERIODO,
                'prestamos_periodo' => $estadisticas['prestamos_periodo'],
                'meses_activos' => $estadisticas['meses_activos'],
                'total_prestamos' => $estadisticas['total_prestamos'],
                'libros_distintos' => $estadisticas['libros_distintos'],
                'puntualidad' => $estadisticas['puntualidad'],
                'bibliotecas' => $estadisticas['bibliotecas'],
                'materias' => array_column($estadisticas['materias'], 'nombre'),
                'sanciones_vigentes' => Sancion::where('user_id', $user->id)
                    ->where('estado', 1)
                    ->whereDate('fecha_fin', '>=', today())
                    ->count(),
            ],
        ]);
    }

    private function resolverNivel(int $prestamos, int $meses): array
    {
        $claves = array_keys(self::NIVELES);
        $actual = 'inicial';

        foreach (self::NIVELES as $clave => $regla) {
            if ($prestamos >= $regla['prestamos'] && $meses >= $regla['meses']) {
                $actual = $clave;
            }
        }

        $nivel = ['clave' => $actual] + self::NIVELES[$actual];
        $indice = array_search($actual, $claves, true);
        $claveSiguiente = $claves[$indice + 1] ?? null;

        if (! $claveSiguiente) {
            return [$nivel, null];
        }

        $regla = self::NIVELES[$claveSiguiente];
        // El progreso lo marca el criterio mas atrasado.
        $progreso = min(
            $regla['prestamos'] > 0 ? $prestamos / $regla['prestamos'] : 1,
            $regla['meses'] > 0 ? $meses / $regla['meses'] : 1
        );

        return [$nivel, [
            'clave' => $claveSiguiente,
            'nombre' => $regla['nombre'],
            'prestamos' => $regla['prestamos'],
            'meses' => $regla['meses'],
            'faltan_prestamos' => max(0, $regla['prestamos'] - $prestamos),
            'faltan_meses' => max(0, $regla['meses'] - $meses),
            'progreso' => (int) floor(min(1, $progreso) * 100),
        ]];
    }

    private function serieMensual(Collection $enPeriodo, Carbon $desde): array
    {
        $porMes = $enPeriodo->countBy(fn ($p) => $p->fecha_prestamo->format('Y-m'));
        $serie = [];

        for ($i = 0; $i < self::MESES_PERIODO; $i++) {
            $mes = $desde->copy()->addMonths($i);
            $serie[] = [
                'clave' => $mes->format('Y-m'),
                'corto' => rtrim(Str::ucfirst($mes->locale('es')->translatedFormat('M')), '.'),
                'largo' => $mes->locale('es')->translatedFormat('F Y'),
                'total' => $porMes[$mes->format('Y-m')] ?? 0,
                'actual' => $mes->isSameMonth(now()),
            ];
        }

        return $serie;
    }

    private function materiasPrincipales(Collection $prestamos, int $limite = 4): array
    {
        return $prestamos
            ->flatMap(fn ($p) => $p->ejemplar?->libro?->materias?->pluck('nombre') ?? [])
            ->filter()
            ->countBy()
            ->sortDesc()
            ->take($limite)
            ->map(fn ($total, $nombre) => ['nombre' => Str::ucfirst(mb_strtolower($nombre)), 'total' => $total])
            ->values()
            ->all();
    }

    private function nuevoCodigo(): string
    {
        // Sin caracteres ambiguos (0/O, 1/I).
        $alfabeto = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        do {
            $bloques = [];
            for ($b = 0; $b < 2; $b++) {
                $bloque = '';
                for ($i = 0; $i < 4; $i++) {
                    $bloque .= $alfabeto[random_int(0, strlen($alfabeto) - 1)];
                }
                $bloques[] = $bloque;
            }
            $codigo = 'CL-' . implode('-', $bloques);
        } while (ConstanciaLector::where('codigo', $codigo)->exists());

        return $codigo;
    }
}
