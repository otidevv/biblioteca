@extends('layouts.admin')

@section('page-title', 'Resumen del sistema')

@section('js')
@if(($visitasPorDia ?? collect())->isNotEmpty())
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    const raw   = @json($visitasPorDia ?? []);
    const today = new Date();

    const labels = [];
    const values = [];

    for (let i = 29; i >= 0; i--) {
        const d = new Date(today);
        d.setDate(d.getDate() - i);
        const key = d.toISOString().slice(0, 10);
        labels.push(d.toLocaleDateString('es-PE', { day: '2-digit', month: 'short' }));
        values.push(raw[key] ?? 0);
    }

    new Chart(document.getElementById('chartVisitas'), {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Visitas',
                data: values,
                backgroundColor: 'rgba(37,99,235,.18)',
                borderColor:     'rgba(37,99,235,.7)',
                borderWidth: 1.5,
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 } },
                x: { grid: { display: false } }
            }
        }
    });
})();
</script>
@endif
@endsection

@section('content')
<div class="admin-dashboard">
    <section class="admin-dashboard__hero">
        <div class="admin-dashboard__eyebrow">Biblioteca UNAMAD</div>
        <h2 class="admin-dashboard__headline">Administra catalogos, lectores y circulacion desde un panel mas claro y rapido.</h2>
        <p class="admin-dashboard__copy">
            Centraliza la operacion diaria de la biblioteca, revisa el estado general del sistema y entra rapido a los modulos que mas usas.
        </p>

        <div class="admin-dashboard__actions">
            <a href="{{ url('/administracion/libros') }}" class="admin-cta admin-cta--primary">Gestionar libros</a>
            <a href="{{ route('administracion.libros.importar') }}" class="admin-cta admin-cta--secondary">Importar libros Excel</a>
            <a href="{{ url('/prestamos/registro') }}" class="admin-cta admin-cta--secondary">Ir a prestamos</a>
            <a href="{{ route('manual.aprendizaje.clasificacion') }}" class="admin-cta admin-cta--secondary">Aprendizaje Dewey y Cutter</a>
        </div>
    </section>

    <section class="admin-dashboard__stats">
        <article class="admin-stat">
            <div class="admin-stat__label">Catalogo</div>
            <div class="admin-stat__value">{{ number_format($totalLibros ?? 0) }}</div>
            <div class="admin-stat__hint">Registros bibliograficos disponibles en el sistema.</div>
        </article>

        <article class="admin-stat">
            <div class="admin-stat__label">Usuarios</div>
            <div class="admin-stat__value">{{ number_format($totalUsuarios ?? 0) }}</div>
            <div class="admin-stat__hint">Cuentas activas para administracion y atencion a lectores.</div>
        </article>

        <article class="admin-stat">
            <div class="admin-stat__label">Prestamos activos</div>
            <div class="admin-stat__value">{{ number_format($prestamosActivos ?? 0) }}</div>
            <div class="admin-stat__hint">Material actualmente en circulacion o pendiente de devolucion.</div>
        </article>

        <article class="admin-stat">
            <div class="admin-stat__label">Bibliotecas</div>
            <div class="admin-stat__value">{{ number_format($totalBibliotecas ?? 0) }}</div>
            <div class="admin-stat__hint">Sedes y puntos de atencion disponibles en la red.</div>
        </article>
    </section>

    <section class="admin-dashboard__grid">
        <article class="admin-card">
            <div class="admin-card__header">
                <div>
                    <div class="admin-card__eyebrow">Accesos rapidos</div>
                    <h3 class="admin-card__title">Modulos principales</h3>
                </div>
            </div>

            <div class="admin-quick-links">
                <a href="{{ url('/administracion/libros') }}" class="admin-quick-link">
                    <div class="admin-quick-link__icon">L</div>
                    <div class="admin-quick-link__title">Libros</div>
                    <div class="admin-quick-link__copy">Consulta, edita y organiza el catalogo bibliografico.</div>
                </a>

                <a href="{{ url('/administracion/libros_nuevo') }}" class="admin-quick-link">
                    <div class="admin-quick-link__icon">+</div>
                    <div class="admin-quick-link__title">Nuevo libro</div>
                    <div class="admin-quick-link__copy">Registra material nuevo y completa sus datos tecnicos.</div>
                </a>

                <a href="{{ route('administracion.libros.importar') }}" class="admin-quick-link">
                    <div class="admin-quick-link__icon">XL</div>
                    <div class="admin-quick-link__title">Importar libros</div>
                    <div class="admin-quick-link__copy">Carga archivos Excel en un modulo aislado para registrar libros sin tocar el flujo actual.</div>
                </a>

                <a href="{{ url('/administracion/traslados_ejemplares') }}" class="admin-quick-link">
                    <div class="admin-quick-link__icon">TR</div>
                    <div class="admin-quick-link__title">Traslados de ejemplares</div>
                    <div class="admin-quick-link__copy">Acepta, rechaza o cancela movimientos de ejemplares desde una vista separada.</div>
                </a>

                <a href="{{ url('/lectores/registro') }}" class="admin-quick-link">
                    <div class="admin-quick-link__icon">U</div>
                    <div class="admin-quick-link__title">Lectores</div>
                    <div class="admin-quick-link__copy">Gestiona lectores, historiales y seguimiento de usuarios.</div>
                </a>

                <a href="{{ url('/prestamos/reservas') }}" class="admin-quick-link">
                    <div class="admin-quick-link__icon">P</div>
                    <div class="admin-quick-link__title">Reservas y prestamos</div>
                    <div class="admin-quick-link__copy">Atiende la circulacion diaria y el estado del material.</div>
                </a>

                <a href="{{ route('manual.aprendizaje.clasificacion') }}" class="admin-quick-link">
                    <div class="admin-quick-link__icon">AI</div>
                    <div class="admin-quick-link__title">Aprendizaje Dewey y Cutter</div>
                    <div class="admin-quick-link__copy">Explica como el sistema aprende de titulos, autores y correcciones de clasificacion.</div>
                </a>
            </div>
        </article>

        <article class="admin-card">
            <div class="admin-card__header">
                <div>
                    <div class="admin-card__eyebrow">Actividad reciente</div>
                    <h3 class="admin-card__title">Ultimos libros registrados</h3>
                </div>
            </div>

            @if(($librosRecientes ?? collect())->isNotEmpty())
                <div class="admin-feed">
                    @foreach($librosRecientes as $libro)
                        <div class="admin-feed__item">
                            <div class="admin-feed__badge">{{ strtoupper(substr($libro->titulo, 0, 1)) }}</div>
                            <div>
                                <div class="admin-feed__title">{{ $libro->titulo }}</div>
                                <div class="admin-feed__meta">
                                    {{ $libro->autores->pluck('nombres')->join(', ') ?: 'Sin autor registrado' }}
                                    @if($libro->editorial)
                                        · {{ $libro->editorial->nombre }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="admin-empty">Aun no hay libros recientes para mostrar en este panel.</div>
            @endif
        </article>
    </section>

    <section class="admin-dashboard__visits">
        <div class="admin-visits__header">
            <div>
                <div class="admin-card__eyebrow">Trafico del sistema</div>
                <h3 class="admin-card__title">Visitas a la web publica</h3>
            </div>
            <span class="admin-visits__note">Sesiones unicas por dia en paginas publicas</span>
        </div>

        <div class="admin-visits__stats">
            <div class="admin-visits__stat">
                <span class="admin-visits__stat-label">Hoy</span>
                <strong class="admin-visits__stat-value">{{ number_format($visitasHoy ?? 0) }}</strong>
            </div>
            <div class="admin-visits__stat">
                <span class="admin-visits__stat-label">Esta semana</span>
                <strong class="admin-visits__stat-value">{{ number_format($visitasSemana ?? 0) }}</strong>
            </div>
            <div class="admin-visits__stat">
                <span class="admin-visits__stat-label">Este mes</span>
                <strong class="admin-visits__stat-value">{{ number_format($visitasMes ?? 0) }}</strong>
            </div>
            <div class="admin-visits__stat admin-visits__stat--total">
                <span class="admin-visits__stat-label">Total historico</span>
                <strong class="admin-visits__stat-value">{{ number_format($visitasTotal ?? 0) }}</strong>
            </div>
        </div>

        @if(($visitasPorDia ?? collect())->isNotEmpty())
        <div class="admin-visits__chart-wrap">
            <canvas id="chartVisitas" height="80"></canvas>
        </div>
        @endif
    </section>

    <div class="admin-footer-note">
        Panel institucional de gestion bibliotecaria UNAMAD.
    </div>
</div>
@endsection
