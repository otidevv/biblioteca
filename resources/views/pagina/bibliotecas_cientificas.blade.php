@extends('layouts.biblioteca')

@section('title', 'Biblioteca UNAMAD | Bibliotecas Científicas')
@section('meta_description', 'Accede a las bibliotecas científicas suscritas por UNAMAD: ScienceDirect, Scopus e IOPScience.')

@section('content')
<style>
/* ── Página principal ── */
.sci-page {
    display: grid;
    gap: 1.75rem;
}

/* ── Hero ── */
.sci-hero {
    position: relative;
    overflow: hidden;
    padding: 2rem 2rem 1.75rem;
    border-radius: 1.5rem;
    border: 1px solid rgba(24, 77, 59, 0.1);
    background:
        radial-gradient(circle at top right, rgba(216, 177, 92, 0.22), transparent 30%),
        linear-gradient(135deg, rgba(255,255,255,0.97), rgba(235,246,241,0.94));
}

.sci-hero::before {
    content: "";
    position: absolute;
    inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23184d3b' fill-opacity='0.025'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}

.sci-hero::after {
    content: "";
    position: absolute;
    bottom: -50px;
    right: -50px;
    width: 220px;
    height: 220px;
    background: radial-gradient(circle, rgba(47,122,93,0.12), transparent 65%);
    pointer-events: none;
}

.sci-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    margin-bottom: 1rem;
    padding: 0.35rem 0.85rem;
    border-radius: 999px;
    background: rgba(24, 77, 59, 0.09);
    color: #1d5a46;
    font-size: 0.75rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.sci-hero h2 {
    margin: 0 0 0.65rem;
    font-size: clamp(1.7rem, 2.5vw, 2.4rem);
    font-weight: 800;
    color: #143529;
    line-height: 1.15;
}

.sci-hero p {
    max-width: 720px;
    margin: 0 0 1.25rem;
    color: #526761;
    line-height: 1.7;
    font-size: 0.97rem;
}

.sci-hero-stats {
    display: flex;
    gap: 1.5rem;
    flex-wrap: wrap;
}

.sci-stat {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.85rem;
    color: #1d5a46;
    font-weight: 600;
}

.sci-stat i {
    font-size: 1rem;
    color: #d8b15c;
}

/* ── Grid de cards ── */
.sci-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1.5rem;
}

/* ── Card individual ── */
.sci-card {
    display: flex;
    flex-direction: column;
    border-radius: 1.25rem;
    border: 1px solid rgba(24, 77, 59, 0.1);
    background: #fff;
    overflow: hidden;
    transition: transform 0.22s ease, box-shadow 0.22s ease;
    box-shadow: 0 2px 12px rgba(20, 53, 41, 0.06);
}

.sci-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(20, 53, 41, 0.13);
}

.sci-card-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem 1.5rem 1.25rem;
    border-bottom: 1px solid rgba(24, 77, 59, 0.07);
    background: linear-gradient(135deg, rgba(240,247,244,0.8), rgba(255,255,255,0.95));
}

.sci-card-logo {
    flex-shrink: 0;
    width: 90px;
    height: 52px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fff;
    border-radius: 0.75rem;
    border: 1px solid rgba(0,0,0,0.07);
    padding: 0.4rem 0.6rem;
    box-shadow: 0 1px 6px rgba(0,0,0,0.06);
}

.sci-card-logo img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.sci-card-title-wrap h3 {
    margin: 0 0 0.2rem;
    font-size: 1.15rem;
    font-weight: 800;
    color: #143529;
}

.sci-card-title-wrap span {
    font-size: 0.78rem;
    font-weight: 600;
    color: #526761;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.sci-card-body {
    flex: 1;
    padding: 1.25rem 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.sci-card-description {
    margin: 0;
    font-size: 0.9rem;
    color: #526761;
    line-height: 1.65;
}

/* ── Badge de acceso ── */
.sci-access-badge {
    display: inline-flex;
    align-items: flex-start;
    gap: 0.6rem;
    padding: 0.75rem 1rem;
    border-radius: 0.85rem;
    background: rgba(24, 77, 59, 0.06);
    border: 1px solid rgba(24, 77, 59, 0.1);
}

.sci-access-badge i {
    font-size: 1.1rem;
    color: #1a7357;
    margin-top: 1px;
    flex-shrink: 0;
}

.sci-access-badge-text {
    display: flex;
    flex-direction: column;
    gap: 0.1rem;
}

.sci-access-badge-text strong {
    font-size: 0.82rem;
    font-weight: 700;
    color: #143529;
}

.sci-access-badge-text small {
    font-size: 0.78rem;
    color: #526761;
    line-height: 1.4;
}

/* ── Vigencia ── */
.sci-vigencia {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.4rem 0.8rem;
    border-radius: 999px;
    background: rgba(216,177,92,0.12);
    border: 1px solid rgba(216,177,92,0.35);
    font-size: 0.78rem;
    font-weight: 600;
    color: #7a6020;
}

.sci-vigencia i {
    font-size: 0.82rem;
    color: #d8b15c;
}

/* ── Footer del card ── */
.sci-card-footer {
    padding: 1rem 1.5rem 1.5rem;
}

.sci-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    width: 100%;
    padding: 0.75rem 1rem;
    border-radius: 0.85rem;
    font-size: 0.9rem;
    font-weight: 700;
    text-decoration: none;
    color: #fff;
    background: linear-gradient(135deg, #184d3b, #1a7357);
    border: none;
    transition: opacity 0.18s, transform 0.18s, box-shadow 0.18s;
    box-shadow: 0 3px 12px rgba(24,77,59,0.28);
}

.sci-btn:hover {
    opacity: 0.9;
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(24,77,59,0.35);
    color: #fff;
    text-decoration: none;
}

.sci-btn i {
    font-size: 1rem;
}

/* ── Badge: variante red vs cuenta ── */
.sci-access-badge--red {
    background: rgba(35, 118, 182, 0.07);
    border-color: rgba(35, 118, 182, 0.18);
}
.sci-access-badge--red i { color: #1a6eb0; }
.sci-access-badge--red .sci-access-badge-text strong { color: #0f3f6a; }

.sci-access-badge--cuenta {
    background: rgba(24, 77, 59, 0.06);
    border-color: rgba(24, 77, 59, 0.14);
}
.sci-access-badge--cuenta i { color: #1a7357; }

/* ── Notas informativas ── */
.sci-nota {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 1rem 1.25rem;
    border-radius: 1rem;
    font-size: 0.875rem;
    line-height: 1.65;
}

.sci-nota i {
    font-size: 1.1rem;
    margin-top: 2px;
    flex-shrink: 0;
}

.sci-nota--cuenta {
    background: rgba(24, 77, 59, 0.07);
    border: 1px solid rgba(24, 77, 59, 0.15);
    color: #1a4030;
}
.sci-nota--cuenta i { color: #1a7357; }

.sci-nota--red {
    background: rgba(35, 118, 182, 0.07);
    border: 1px solid rgba(35, 118, 182, 0.18);
    color: #0f3f6a;
}
.sci-nota--red i { color: #1a6eb0; }

.sci-nota--ayuda {
    background: rgba(216, 177, 92, 0.1);
    border: 1px solid rgba(216, 177, 92, 0.28);
    color: #6b5020;
}
.sci-nota--ayuda i { color: #c89f40; }

/* ── Dark mode ── */
.library-dark .sci-hero {
    background:
        radial-gradient(circle at top right, rgba(216,177,92,0.12), transparent 30%),
        linear-gradient(135deg, rgba(30,45,38,0.96), rgba(22,37,30,0.98));
    border-color: rgba(255,255,255,0.07);
}

.library-dark .sci-hero h2 { color: #e8f0ec; }
.library-dark .sci-hero p  { color: #a0b5ab; }
.library-dark .sci-stat    { color: #7ecfaa; }

.library-dark .sci-card {
    background: rgba(30,45,38,0.9);
    border-color: rgba(255,255,255,0.07);
    box-shadow: 0 2px 12px rgba(0,0,0,0.25);
}

.library-dark .sci-card:hover {
    box-shadow: 0 12px 40px rgba(0,0,0,0.4);
}

.library-dark .sci-card-header {
    background: linear-gradient(135deg, rgba(24,77,59,0.3), rgba(20,53,41,0.2));
    border-color: rgba(255,255,255,0.06);
}

.library-dark .sci-card-logo {
    background: rgba(255,255,255,0.06);
    border-color: rgba(255,255,255,0.1);
}

.library-dark .sci-card-title-wrap h3 { color: #e0ede8; }
.library-dark .sci-card-title-wrap span { color: #7fa896; }
.library-dark .sci-card-description { color: #90a89f; }

.library-dark .sci-access-badge {
    background: rgba(26,115,87,0.15);
    border-color: rgba(26,115,87,0.2);
}

.library-dark .sci-access-badge-text strong { color: #c4ddd5; }
.library-dark .sci-access-badge-text small  { color: #7fa896; }

.library-dark .sci-vigencia {
    background: rgba(216,177,92,0.08);
    border-color: rgba(216,177,92,0.2);
    color: #d8b15c;
}

.library-dark .sci-nota--cuenta {
    background: rgba(26,115,87,0.1);
    border-color: rgba(26,115,87,0.2);
    color: #7ecfaa;
}
.library-dark .sci-nota--red {
    background: rgba(35,118,182,0.1);
    border-color: rgba(35,118,182,0.2);
    color: #7ab8e8;
}
.library-dark .sci-nota--ayuda {
    background: rgba(216,177,92,0.07);
    border-color: rgba(216,177,92,0.18);
    color: #c4a852;
}
.library-dark .sci-access-badge--red {
    background: rgba(35,118,182,0.12);
    border-color: rgba(35,118,182,0.2);
}
.library-dark .sci-access-badge--red .sci-access-badge-text strong { color: #7ab8e8; }
.library-dark .sci-access-badge--red i { color: #5aaae0; }

/* ── Responsive ── */
@media (max-width: 600px) {
    .sci-hero { padding: 1.25rem; }
    .sci-hero-stats { gap: 0.85rem; }
    .sci-grid { grid-template-columns: 1fr; }
}
</style>

<div class="sci-page">

    {{-- Hero --}}
    <div class="sci-hero">
        <div class="sci-eyebrow">
            <i class="bi bi-journal-medical"></i>
            Recursos de investigación
        </div>
        <h2>Bibliotecas Científicas</h2>
        <p>
            Accede a las plataformas de investigación suscritas por UNAMAD. Consulta artículos,
            revistas y libros científicos revisados por pares de las principales editoriales mundiales.
        </p>
        <div class="sci-hero-stats">
            <div class="sci-stat">
                <i class="bi bi-database-fill-check"></i>
                3 bases de datos activas
            </div>
            <div class="sci-stat">
                <i class="bi bi-globe2"></i>
                Acceso desde la institución
            </div>
            <div class="sci-stat">
                <i class="bi bi-shield-fill-check"></i>
                Recursos verificados
            </div>
        </div>
    </div>

    {{-- Grid de bases de datos --}}
    <div class="sci-grid">
        @foreach ($bases as $base)
        <div class="sci-card">
            <div class="sci-card-header">
                <div class="sci-card-logo">
                    <img src="{{ asset($base['logo']) }}" alt="Logo {{ $base['nombre'] }}">
                </div>
                <div class="sci-card-title-wrap">
                    <h3>{{ $base['nombre'] }}</h3>
                    <span>Base de datos científica</span>
                </div>
            </div>

            <div class="sci-card-body">
                <p class="sci-card-description">{{ $base['descripcion'] }}</p>

                <div class="sci-access-badge sci-access-badge--{{ $base['tipo_acceso'] }}">
                    @if ($base['tipo_acceso'] === 'cuenta')
                        <i class="bi bi-person-fill-check"></i>
                    @else
                        <i class="bi bi-wifi"></i>
                    @endif
                    <div class="sci-access-badge-text">
                        <strong>{{ $base['acceso'] }}</strong>
                        <small>{{ $base['detalle'] }}</small>
                    </div>
                </div>

                @if ($base['vigencia'])
                <div>
                    <span class="sci-vigencia">
                        <i class="bi bi-calendar2-range-fill"></i>
                        Vigencia: {{ $base['vigencia'] }}
                    </span>
                </div>
                @endif
            </div>

            <div class="sci-card-footer">
                <a href="{{ $base['url'] }}" target="_blank" rel="noopener noreferrer" class="sci-btn">
                    <i class="bi bi-box-arrow-up-right"></i>
                    Ingresar a {{ $base['nombre'] }}
                </a>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Notas informativas --}}
    <div style="display:grid; gap:0.75rem;">
        <div class="sci-nota sci-nota--cuenta">
            <i class="bi bi-person-fill-check"></i>
            <span>
                <strong>ScienceDirect y Scopus — acceso por cuenta:</strong>
                Ingresa a la plataforma, haz clic en <em>"Register"</em> o <em>"Create account"</em>
                y usa tu correo <strong>@unamad.edu.pe</strong> para registrarte. Una vez creada la cuenta,
                tendrás acceso completo de forma gratuita.
            </span>
        </div>
        <div class="sci-nota sci-nota--red">
            <i class="bi bi-wifi"></i>
            <span>
                <strong>IOPScience — acceso por red institucional:</strong>
                Solo necesitas estar conectado al <strong>WiFi o la red de cable de UNAMAD</strong>.
                El acceso es automático, sin contraseña ni registro. No funcionará desde redes externas
                (tu casa, datos móviles, etc.).
            </span>
        </div>
        <div class="sci-nota sci-nota--ayuda">
            <i class="bi bi-headset"></i>
            <span>
                ¿Tienes dificultades para acceder? Comunícate con la
                <strong>Biblioteca Central de UNAMAD</strong> para recibir orientación personalizada.
            </span>
        </div>
    </div>

</div>
@endsection
