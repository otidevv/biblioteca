<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Biblioteca UNAMAD | Inicio</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="Portada de la Biblioteca UNAMAD con acceso al catálogo, bibliotecas disponibles y libros recientes.">

<link rel="icon" type="image/png" href="{{ asset('img/logo_unamad.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Archivo:wght@500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link href="{{ asset('css/pagina/index.css') }}" rel="stylesheet">

<style>
:root {
    --ld-bg: #fdfcfa;
    --ld-ink: #1a1a2e;
    --ld-ink-soft: #4a4a5e;
    --ld-ink-mute: #5a5a6e;
    --ld-accent: #db0455;
}

html, body {
    margin: 0;
    padding: 0;
    background: var(--ld-bg);
    color: var(--ld-ink);
    font-family: 'Space Grotesk', sans-serif;
}

.ld-page {
    min-height: 100vh;
    background: var(--ld-bg);
    overflow-x: hidden;
    display: flex;
    flex-direction: column;
}

.ld-page a {
    color: var(--ld-ink);
    text-decoration: none;
}
.ld-page a:hover { color: var(--ld-accent); }

/* ── Header ── */
.ld-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 14px 48px;
    border-bottom: 1px solid rgba(26, 26, 46, 0.06);
}

.ld-brand {
    display: flex;
    align-items: center;
    gap: 10px;
    font-family: 'Archivo', sans-serif;
    font-weight: 700;
    font-size: 20px;
    letter-spacing: -0.02em;
    white-space: nowrap;
}

.ld-brand-mark {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: #fff;
    border: 1px solid rgba(26, 26, 46, 0.08);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    flex-shrink: 0;
}

.ld-brand-mark img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    padding: 4px;
}

.ld-brand small {
    display: block;
    font-family: 'Space Grotesk', sans-serif;
    font-weight: 500;
    font-size: 10.5px;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--ld-ink-mute);
}

.ld-nav {
    display: flex;
    gap: 28px;
    font-size: 14px;
    font-weight: 500;
}

.ld-actions {
    display: flex;
    align-items: center;
    gap: 8px;
}

.ld-cta {
    background: var(--ld-ink);
    color: #fff !important;
    font-size: 13px;
    font-weight: 600;
    padding: 10px 20px;
    border-radius: 999px;
    white-space: nowrap;
    transition: background 0.18s ease;
}
.ld-cta:hover { background: var(--ld-accent); }

.ld-cta-circle {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: var(--ld-accent);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #fff !important;
    font-size: 14px;
    flex-shrink: 0;
    transition: transform 0.18s ease;
}
.ld-cta-circle:hover { transform: scale(1.08); }

/* ── Hero ── */
.ld-hero {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 64px 24px 0;
}

.ld-badge {
    border: 1px solid rgba(26, 26, 46, 0.18);
    border-radius: 999px;
    padding: 7px 18px;
    font-size: 13px;
    font-weight: 500;
    color: var(--ld-ink-soft);
    background: #fff;
}

.ld-hero h1 {
    font-family: 'Archivo', sans-serif;
    font-weight: 600;
    font-size: clamp(2.1rem, 5vw, 4rem);
    line-height: 1.08;
    letter-spacing: -0.03em;
    max-width: 900px;
    margin: 28px 0 0;
    text-wrap: balance;
}

.ld-hero h1 span { color: var(--ld-accent); }

.ld-hero p {
    max-width: 560px;
    font-size: 15px;
    line-height: 1.6;
    color: var(--ld-ink-mute);
    margin: 22px 0 0;
    text-wrap: pretty;
}

.ld-search {
    display: flex;
    align-items: center;
    gap: 6px;
    width: min(560px, 100%);
    margin-top: 26px;
    padding: 6px 6px 6px 20px;
    background: #fff;
    border: 1px solid rgba(26, 26, 46, 0.14);
    border-radius: 999px;
    box-shadow: 0 12px 30px -14px rgba(26, 26, 46, 0.18);
}

.ld-search i {
    color: var(--ld-ink-mute);
    font-size: 15px;
}

.ld-search input {
    flex: 1;
    min-width: 0;
    border: 0;
    outline: none;
    background: transparent;
    font-family: 'Space Grotesk', sans-serif;
    font-size: 14.5px;
    color: var(--ld-ink);
}

.ld-search button {
    border: 0;
    background: var(--ld-accent);
    color: #fff;
    font-family: 'Space Grotesk', sans-serif;
    font-weight: 600;
    font-size: 13.5px;
    padding: 11px 22px;
    border-radius: 999px;
    cursor: pointer;
    white-space: nowrap;
    transition: background 0.18s ease;
}
.ld-search button:hover { background: var(--ld-ink); }

/* ── Carrusel en abanico ── */
.ld-fan {
    margin-top: 36px;
    position: relative;
    height: 520px;
    overflow: hidden;
}

.ld-fan-track {
    display: flex;
    width: max-content;
    gap: 44px;
    padding: 70px 0 30px;
    will-change: transform;
}

.ld-fan-card {
    width: 230px;
    height: 390px;
    border-radius: 20px;
    box-shadow: 0 24px 48px -18px rgba(26, 26, 46, 0.35);
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    padding: 22px;
    box-sizing: border-box;
    position: relative;
    overflow: hidden;
    flex-shrink: 0;
    will-change: transform;
}

.ld-fan-card .ld-fan-cover {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.ld-fan-card .ld-fan-scrim {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, transparent 42%, rgba(12, 12, 24, 0.88) 100%);
}

.ld-fan-card .ld-fan-hint {
    position: relative;
    font-family: monospace;
    font-size: 11px;
    color: rgba(255, 255, 255, 0.75);
    letter-spacing: 0.04em;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.ld-fan-card .ld-fan-title {
    position: relative;
    font-family: 'Archivo', sans-serif;
    font-weight: 700;
    font-size: 21px;
    line-height: 1.15;
    color: #fff;
    text-transform: uppercase;
    margin-top: 6px;
}

.ld-fan-card .ld-fan-title span { color: var(--ld-accent); }

/* ── Marquesina de recursos ── */
.ld-marquee-wrap {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 20px;
    padding: 8px 24px 56px;
}

.ld-marquee-label {
    font-size: 13px;
    color: var(--ld-ink-mute);
    text-align: center;
}

.ld-marquee-mask {
    max-width: 820px;
    width: 100%;
    overflow: hidden;
    -webkit-mask-image: linear-gradient(90deg, transparent, #000 12%, #000 88%, transparent);
    mask-image: linear-gradient(90deg, transparent, #000 12%, #000 88%, transparent);
}

.ld-marquee-track {
    display: flex;
    width: max-content;
    color: #3a4356;
    animation: ld-marquee 45s linear infinite;
}

@keyframes ld-marquee {
    from { transform: translateX(0); }
    to   { transform: translateX(-50%); }
}

.ld-logo-set {
    display: flex;
    align-items: center;
    gap: 48px;
    padding-right: 48px;
    flex-shrink: 0;
}

.ld-logo-set > span { white-space: nowrap; }

/* ── Nuestras Bibliotecas (bento grid) ── */
.ld-libs {
    padding: 40px 0 8px;
}

.ld-libs-header {
    max-width: 640px;
    margin-bottom: 32px;
}

.ld-libs-header h2 {
    font-family: 'Archivo', sans-serif;
    font-weight: 600;
    font-size: clamp(1.7rem, 3.2vw, 2.6rem);
    line-height: 1.1;
    letter-spacing: -0.03em;
    margin: 0;
    text-align: left;
}

.ld-libs-header h2 span { color: var(--ld-accent); }

.ld-libs-header p {
    font-size: 15px;
    line-height: 1.6;
    color: var(--ld-ink-mute);
    margin: 12px 0 0;
    text-align: left;
}

.ld-libs-grid {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 20px;
}

.ld-lib-card {
    flex: 0 0 calc(25% - 15px);
    max-width: calc(25% - 15px);
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding: 24px 22px;
    background: #fff;
    border: 1px solid rgba(26, 26, 46, 0.08);
    border-radius: 18px;
    color: var(--ld-ink) !important;
    opacity: 0;
    transform: translateY(26px);
    transition:
        opacity 0.55s ease,
        transform 0.55s ease,
        box-shadow 0.25s ease,
        border-color 0.25s ease;
}

.ld-lib-card.is-visible {
    opacity: 1;
    transform: translateY(0);
}

.ld-lib-card:hover {
    border-color: var(--ld-accent);
    box-shadow: 0 22px 44px -18px rgba(26, 26, 46, 0.28);
    transform: translateY(-4px) scale(1.02);
    transition:
        opacity 0.55s ease,
        transform 0.25s ease,
        box-shadow 0.25s ease,
        border-color 0.25s ease;
}

.ld-lib-banner {
    display: block;
    margin: -24px -22px 2px;
    aspect-ratio: 3 / 2;
    overflow: hidden;
    border-radius: 17px 17px 0 0;
    background: rgba(26, 26, 46, 0.04);
}

.ld-lib-banner img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.35s ease;
}

.ld-lib-card:hover .ld-lib-banner img {
    transform: scale(1.06);
}

.ld-lib-card.has-img .ld-lib-icon {
    margin-top: -45px;
    position: relative;
    background: #fff;
    border: 3px solid #fff;
    box-shadow: 0 8px 18px rgba(26, 26, 46, 0.14);
}

.ld-lib-icon {
    width: 46px;
    height: 46px;
    border-radius: 13px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: var(--ld-accent);
    background: rgba(219, 4, 85, 0.08);
    transition: background 0.25s ease, color 0.25s ease;
}

.ld-lib-card:hover .ld-lib-icon {
    background: var(--ld-accent);
    color: #fff;
}

.ld-lib-card h5 {
    font-family: 'Archivo', sans-serif;
    font-weight: 700;
    font-size: 15.5px;
    line-height: 1.25;
    letter-spacing: -0.01em;
    margin: 0;
}

.ld-lib-card p {
    font-size: 13px;
    line-height: 1.5;
    color: var(--ld-ink-mute);
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.ld-lib-card .ld-lib-more {
    margin-top: auto;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--ld-accent);
    opacity: 0;
    transform: translateX(-4px);
    transition: opacity 0.25s ease, transform 0.25s ease;
}

.ld-lib-card:hover .ld-lib-more {
    opacity: 1;
    transform: translateX(0);
}

@media (max-width: 991.98px) {
    .ld-lib-card {
        flex: 0 0 calc(50% - 10px);
        max-width: calc(50% - 10px);
    }
}

@media (max-width: 575.98px) {
    .ld-lib-card {
        flex: 0 0 100%;
        max-width: 100%;
    }
}

@media (prefers-reduced-motion: reduce) {
    .ld-lib-card {
        opacity: 1;
        transform: none;
        transition: box-shadow 0.25s ease, border-color 0.25s ease;
    }
}

/* ── Secciones de contenido existentes ── */
.ld-sections {
    max-width: 1200px;
    width: 100%;
    margin: 0 auto;
    padding: 0 24px 48px;
    box-sizing: border-box;
}

.ld-sections .home-section { margin-top: 2.5rem; }

/* ── Footer ── */
.ld-footer {
    margin-top: auto;
    background: var(--ld-ink);
    color: rgba(255, 255, 255, 0.72);
}

.ld-footer a { color: rgba(255, 255, 255, 0.72); }
.ld-footer a:hover { color: var(--ld-accent); }

.ld-footer-inner {
    max-width: 1200px;
    margin: 0 auto;
    padding: 48px 24px 32px;
    display: grid;
    grid-template-columns: 1.6fr 1fr 1fr;
    gap: 40px;
}

.ld-footer-brand {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.ld-footer-brand-row {
    display: flex;
    align-items: center;
    gap: 10px;
    font-family: 'Archivo', sans-serif;
    font-weight: 700;
    font-size: 18px;
    color: #fff;
}

.ld-footer-brand-row .ld-brand-mark { border: 0; }

.ld-footer p {
    font-size: 13.5px;
    line-height: 1.65;
    margin: 0;
    max-width: 380px;
}

.ld-footer h6 {
    font-family: 'Archivo', sans-serif;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: #fff;
    margin: 0 0 14px;
}

.ld-footer-list {
    display: flex;
    flex-direction: column;
    gap: 9px;
    font-size: 13.5px;
}

.ld-footer-bottom {
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.ld-footer-bottom-inner {
    max-width: 1200px;
    margin: 0 auto;
    padding: 16px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    font-size: 12.5px;
    color: rgba(255, 255, 255, 0.45);
}

/* ── Responsive ── */
@media (max-width: 991.98px) {
    .ld-header { padding: 14px 24px; }
    .ld-nav { display: none; }
    .ld-footer-inner { grid-template-columns: 1fr 1fr; }
    .ld-footer-brand { grid-column: 1 / -1; }
}

@media (max-width: 575.98px) {
    .ld-header { padding: 12px 16px; gap: 10px; }
    .ld-brand { font-size: 16px; gap: 8px; }
    .ld-brand small { display: none; }
    .ld-brand-mark { width: 34px; height: 34px; }
    .ld-cta { padding: 9px 14px; font-size: 12.5px; }
    .ld-hide-sm { display: none !important; }
    .ld-hero { padding-top: 44px; }
    .ld-fan { height: 470px; }
    .ld-fan-track { padding-top: 50px; }
    .ld-search { flex-wrap: nowrap; }
    .ld-search button { padding: 10px 16px; }
    .ld-footer-inner { grid-template-columns: 1fr; gap: 28px; }
    .ld-footer-bottom-inner { flex-direction: column; align-items: flex-start; gap: 4px; }
}

@media (prefers-reduced-motion: reduce) {
    .ld-marquee-track { animation: none; }
}
</style>
</head>

<body>
@php
    $user = Auth::user();
    $esLector = $user
        ? $user->usuarioRolBibliotecas()->where('estado', 1)->where('rol_id', 5)->exists()
        : false;

    // Tarjetas del carrusel: los libros recientes con portada que trae el
    // controlador. Si no hay ninguno, la sección no se muestra.
    $fanCards = $libros->map(function ($libro) {
        $autores = $libro->autores
            ->map(fn($a) => trim($a->nombres . ' ' . $a->apellidos))
            ->filter()
            ->implode(', ');
        return [
            'img'    => $libro->imagen,
            'titulo' => \Illuminate\Support\Str::limit($libro->titulo, 44),
            'hint'   => '// ' . ($autores !== '' ? \Illuminate\Support\Str::limit($autores, 34) : 'título reciente'),
            'url'    => route('libro.show', $libro->id),
        ];
    })->values();

    $cardsPerSet = $fanCards->count();
    // Copias suficientes para que el bucle sea continuo en pantallas anchas.
    $fanSets = max(3, (int) ceil(3840 / max(1, $cardsPerSet * 274)) + 1);
@endphp

<div class="ld-page">

    <header class="ld-header">
        <a href="{{ route('home') }}" class="ld-brand">
            <span class="ld-brand-mark">
                <img src="{{ asset('img/logo_unamad.png') }}" alt="Logo UNAMAD">
            </span>
            <span>
                Biblioteca UNAMAD
                <small>Univ. Nac. Amazónica de Madre de Dios</small>
            </span>
        </a>

        <nav class="ld-nav">
            <a href="{{ route('catalogo') }}">Catálogo</a>
            <a href="{{ route('evento') }}">Eventos</a>
            <a href="{{ route('bibliotecas.cientificas') }}">B. Científicas</a>
            <a href="{{ route('otras.bibliotecas') }}">Otras bibliotecas</a>
            @auth
            <a href="{{ route('mis.reservas') }}">Mis reservas</a>
            <a href="{{ route('prestamos') }}">Mis préstamos</a>
            @endauth
        </nav>

        <div class="ld-actions">
            @auth
                <a href="{{ route('perfil.edit', ['layout' => 'library']) }}" class="ld-cta">
                    {{ $user->name }}
                </a>
                @unless($esLector)
                    <a href="{{ route('dashboard') }}" class="ld-cta-circle" title="Panel de administración">↗</a>
                @endunless
                <form action="{{ route('logout') }}" method="POST" class="d-inline m-0">
                    @csrf
                    <button type="submit" class="ld-cta-circle border-0" title="Cerrar sesión" style="cursor:pointer;background:#1a1a2e;">
                        <i class="bi bi-box-arrow-right" style="font-size:13px;"></i>
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="ld-cta">Iniciar sesión</a>
                <a href="{{ route('login') }}" class="ld-cta-circle ld-hide-sm">↗</a>
            @endauth
        </div>
    </header>

    <section class="ld-hero">
        <span class="ld-badge">Universidad Nacional Amazónica de Madre de Dios</span>
        <h1>
            Descubre, consulta y <span>reserva el conocimiento</span> de tu biblioteca universitaria
        </h1>
        <p>
            Accede al catálogo institucional con miles de títulos, revisa la disponibilidad en tiempo
            real por biblioteca y encuentra las publicaciones más recientes desde un solo lugar.
        </p>

        <form action="{{ route('catalogo') }}" method="GET" class="ld-search" role="search">
            <i class="bi bi-search"></i>
            <input type="text"
                   name="titulo"
                   placeholder="Buscar por título, autor o palabra clave"
                   value="{{ request('titulo') }}"
                   aria-label="Buscar en el catálogo">
            <button type="submit">Buscar ahora</button>
        </form>
    </section>

    @if ($fanCards->isNotEmpty())
    <section class="ld-fan" aria-label="Títulos recientes de la biblioteca">
        <div class="ld-fan-track" id="ldFanTrack">
            @for ($set = 0; $set < $fanSets; $set++)
                @foreach ($fanCards as $card)
                    <a href="{{ $card['url'] }}" class="ld-fan-card" data-fan-card="1" tabindex="{{ $set === 0 ? 0 : -1 }}">
                        <img src="{{ $card['img'] }}"
                             alt=""
                             class="ld-fan-cover"
                             loading="lazy"
                             decoding="async"
                             onerror="this.onerror=null;this.src='{{ asset('img/libro-placeholder.png') }}';">
                        <span class="ld-fan-scrim"></span>
                        <span class="ld-fan-hint">{{ $card['hint'] }}</span>
                        <span class="ld-fan-title" style="font-size:17px;text-transform:none;">{{ $card['titulo'] }}</span>
                    </a>
                @endforeach
            @endfor
        </div>
    </section>
    @endif

    <section class="ld-marquee-wrap" aria-label="Recursos de investigación">
        <span class="ld-marquee-label">Recursos que potencian tu investigación</span>
        <div class="ld-marquee-mask">
            <div class="ld-marquee-track">
                @for ($i = 0; $i < 2; $i++)
                <div class="ld-logo-set" @if($i === 1) aria-hidden="true" @endif>
                    <span style="font-family:'Archivo',sans-serif;font-weight:800;font-size:17px;letter-spacing:0.01em;text-transform:uppercase;font-style:italic;">Scopus</span>
                    <span style="font-family:'Archivo',sans-serif;font-weight:700;font-size:19px;letter-spacing:-0.02em;">ScienceDirect</span>
                    <span style="font-family:'Space Grotesk',sans-serif;font-weight:500;font-size:17px;letter-spacing:0.24em;text-transform:uppercase;">IOPscience</span>
                    <span style="font-family:'Archivo',sans-serif;font-weight:700;font-size:19px;letter-spacing:-0.01em;">eLibro<span style="font-size:11px;vertical-align:super;">°</span></span>
                    <span style="display:inline-flex;flex-direction:column;align-items:center;line-height:1.1;">
                        <span style="font-size:10px;letter-spacing:0.2em;text-transform:uppercase;font-weight:500;">Repositorio</span>
                        <span style="font-size:14px;letter-spacing:0.1em;text-transform:uppercase;font-weight:700;border-top:1.5px solid #3a4356;padding-top:2px;">UNAMAD</span>
                    </span>
                    <span style="display:inline-flex;align-items:center;gap:6px;font-family:'Archivo',sans-serif;font-weight:600;font-size:16px;letter-spacing:0.1em;text-transform:uppercase;">
                        <span style="width:13px;height:13px;background:#3a4356;transform:rotate(45deg);display:inline-block;"></span>Concytec
                    </span>
                </div>
                @endfor
            </div>
        </div>
    </section>

    <main class="ld-sections" id="contenido-principal">

        <section class="ld-libs" id="nuestras-bibliotecas" aria-label="Nuestras bibliotecas">
            <div class="ld-libs-header">
                <h2>Nuestras <span>Bibliotecas</span></h2>
                <p>Once espacios de consulta, estudio e investigación al servicio de la comunidad universitaria.</p>
            </div>

            @php
                $libIcons = ['bi-bank', 'bi-cpu', 'bi-heart-pulse', 'bi-mortarboard', 'bi-clipboard2-pulse', 'bi-flower1', 'bi-journal-text', 'bi-globe-americas', 'bi-people', 'bi-lightbulb'];
            @endphp

            <div class="ld-libs-grid" id="ldLibsGrid">
                @foreach($bibliotecas as $b)
                @php
                    // Imagen de la tarjeta: campo `imagen` de la BD o, por
                    // convención, public/img/bibliotecas/{id}.{webp|png|jpg}.
                    $bannerImg = $b->imagen ? asset($b->imagen) : null;
                    if (!$bannerImg) {
                        foreach (['webp', 'png', 'jpg', 'jpeg'] as $ext) {
                            if (file_exists(public_path("img/bibliotecas/{$b->id}.{$ext}"))) {
                                $bannerImg = asset("img/bibliotecas/{$b->id}.{$ext}");
                                break;
                            }
                        }
                    }
                @endphp
                <a href="{{ route('biblioteca.show', $b->id) }}"
                   class="ld-lib-card {{ $bannerImg ? 'has-img' : '' }}"
                   aria-label="Explorar biblioteca {{ $b->nombre }}">
                    @if ($bannerImg)
                    <span class="ld-lib-banner">
                        <img src="{{ $bannerImg }}"
                             alt=""
                             loading="lazy"
                             decoding="async"
                             onerror="var c=this.closest('.ld-lib-card');c.classList.remove('has-img');c.querySelector('.ld-lib-banner').remove();">
                    </span>
                    @endif
                    <span class="ld-lib-icon"><i class="bi {{ $libIcons[$loop->index % count($libIcons)] }}"></i></span>
                    <h5>{{ \Illuminate\Support\Str::title(mb_strtolower($b->nombre)) }}</h5>
                    <p>{{ \Illuminate\Support\Str::limit($b->descripcion ?: 'Espacio de consulta y estudio de la universidad.', 64) }}</p>
                    <span class="ld-lib-more">Explorar <i class="bi bi-arrow-right"></i></span>
                </a>
                @endforeach

                <a href="https://repositorio.unamad.edu.pe/"
                   class="ld-lib-card has-img"
                   target="_blank"
                   rel="noopener noreferrer"
                   aria-label="Explorar el Repositorio Institucional Digital">
                    <span class="ld-lib-banner">
                        <img src="{{ asset('img/repositorio.png') }}"
                             alt=""
                             loading="lazy"
                             decoding="async"
                             onerror="var c=this.closest('.ld-lib-card');c.classList.remove('has-img');c.querySelector('.ld-lib-banner').remove();">
                    </span>
                    <span class="ld-lib-icon"><i class="bi bi-archive"></i></span>
                    <h5>Repositorio Institucional Digital</h5>
                    <p>Producción científica y académica de la universidad, en abierto.</p>
                    <span class="ld-lib-more">Explorar <i class="bi bi-arrow-up-right"></i></span>
                </a>
            </div>
        </section>

        <section class="home-section">
            <div class="home-section-header">
                <div>
                    <h3 class="home-section-title">Actividades en Curso</h3>
                    <p class="home-section-subtitle">Talleres, encuentros, capacitaciones y avisos activos para la comunidad universitaria.</p>
                </div>
                <a href="{{ route('evento') }}" class="home-link">
                    Ver agenda completa <i class="bi bi-chevron-right"></i>
                </a>
            </div>

            <div class="row g-4">
                @forelse($actividades as $actividad)
                <div class="col-12 col-md-6 col-xl-3">
                    <article class="home-activity-card">
                        <div class="home-activity-meta">
                            <span class="home-activity-badge">
                                <i class="bi bi-calendar2-week"></i>
                                {{ $actividad->categoria->nombre ?? 'Actividad' }}
                            </span>
                            <span class="home-activity-date">
                                {{ $actividad->fecha_inicio?->format('d/m/Y') ?? 'Fecha por definir' }}
                            </span>
                        </div>

                        <h6>{{ \Illuminate\Support\Str::limit($actividad->titulo, 70) }}</h6>
                        <div class="home-activity-copy">
                            {{ \Illuminate\Support\Str::limit(strip_tags($actividad->contenido ?: $actividad->referencia ?: 'Actividad disponible en la agenda de biblioteca.'), 120) }}
                        </div>

                        <div class="home-activity-footer">
                            <span>{{ $actividad->referencia ?: 'Biblioteca UNAMAD' }}</span>
                            <a href="{{ route('evento') }}" class="home-link">Ver más</a>
                        </div>
                    </article>
                </div>
                @empty
                <div class="col-12">
                    <div class="alert alert-light border rounded-4 mb-0">
                        No hay actividades activas para mostrar en este momento.
                    </div>
                </div>
                @endforelse
            </div>
        </section>

    </main>

    <footer class="ld-footer" aria-label="Pie de página institucional">
        <div class="ld-footer-inner">
            <div class="ld-footer-brand">
                <div class="ld-footer-brand-row">
                    <span class="ld-brand-mark">
                        <img src="{{ asset('img/logo_unamad.png') }}" alt="Logo UNAMAD">
                    </span>
                    Biblioteca UNAMAD
                </div>
                <p>
                    Plataforma de consulta bibliográfica de la Universidad Nacional Amazónica de
                    Madre de Dios. Explora el catálogo, gestiona reservas y accede a bases de
                    datos científicas.
                </p>
                <p style="font-size:12.5px;">
                    <i class="bi bi-geo-alt-fill" style="color:var(--ld-accent);"></i>
                    Puerto Maldonado, Madre de Dios — Perú
                </p>
            </div>

            <div>
                <h6>Navegación</h6>
                <div class="ld-footer-list">
                    <a href="{{ route('home') }}">Inicio</a>
                    <a href="{{ route('catalogo') }}">Catálogo de libros</a>
                    <a href="{{ route('evento') }}">Eventos y agenda</a>
                    <a href="{{ route('otras.bibliotecas') }}">Otras bibliotecas</a>
                    <a href="{{ route('bibliotecas.cientificas') }}">Bibliotecas científicas</a>
                    @auth
                    <a href="{{ route('mis.reservas') }}">Mis reservas</a>
                    <a href="{{ route('prestamos') }}">Mis préstamos</a>
                    @endauth
                </div>
            </div>

            <div>
                <h6>Contacto y recursos</h6>
                <div class="ld-footer-list">
                    <span><i class="bi bi-envelope-fill me-1"></i> biblioteca@unamad.edu.pe</span>
                    <span><i class="bi bi-clock-fill me-1"></i> Plataforma disponible las 24 horas</span>
                    <a href="https://elibro.net/es/lc/unamad/login_usuario/" target="_blank" rel="noopener noreferrer">
                        eLibro — Biblioteca virtual
                    </a>
                    <a href="https://repositorio.unamad.edu.pe/" target="_blank" rel="noopener noreferrer">
                        Repositorio institucional
                    </a>
                </div>
            </div>
        </div>

        <div class="ld-footer-bottom">
            <div class="ld-footer-bottom-inner">
                <span>© {{ now()->year }} Biblioteca UNAMAD — Todos los derechos reservados.</span>
                <span>Sistema Bibliotecario Institucional</span>
            </div>
        </div>
    </footer>

</div>

<script>
(function () {
    var track = document.getElementById('ldFanTrack');
    if (!track) return;

    var PITCH = 230 + 44;
    var CARDS_PER_SET = {{ $cardsPerSet }};
    var HALF = PITCH * CARDS_PER_SET;
    var SPEED_S = 40;

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    var start = null;
    var raf;

    function tick(now) {
        raf = requestAnimationFrame(tick);
        if (start === null) start = now;

        var dur = SPEED_S * 1000;
        var offset = (((now - start) % dur) / dur) * HALF;
        track.style.transform = 'translateX(' + (-offset) + 'px)';

        var vw = window.innerWidth;
        var cx = vw / 2;
        var cards = track.querySelectorAll('[data-fan-card]');
        cards.forEach(function (el, i) {
            var cardCx = i * PITCH + 115 - offset;
            var n = Math.max(-1, Math.min(1, (cardCx - cx) / (vw / 2)));
            var rot = n * 12;
            var drop = (1 - Math.cos(n * Math.PI / 2)) * 90;
            el.style.transform = 'translateY(' + drop + 'px) rotate(' + rot + 'deg)';
        });
    }

    raf = requestAnimationFrame(tick);

    document.addEventListener('visibilitychange', function () {
        if (document.hidden) {
            cancelAnimationFrame(raf);
            start = null;
        } else {
            raf = requestAnimationFrame(tick);
        }
    });
})();

(function () {
    var cards = document.querySelectorAll('.ld-lib-card');
    if (!cards.length) return;

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches || !('IntersectionObserver' in window)) {
        cards.forEach(function (el) { el.classList.add('is-visible'); });
        return;
    }

    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (!entry.isIntersecting) return;
            var el = entry.target;
            var i = Array.prototype.indexOf.call(cards, el);
            el.style.transitionDelay = (i % 12) * 70 + 'ms';
            el.classList.add('is-visible');
            el.addEventListener('transitionend', function clear() {
                el.style.transitionDelay = '0ms';
                el.removeEventListener('transitionend', clear);
            });
            observer.unobserve(el);
        });
    }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });

    cards.forEach(function (el) { observer.observe(el); });
})();
</script>
</body>
</html>
