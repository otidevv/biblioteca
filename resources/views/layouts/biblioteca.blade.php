<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>@yield('title', 'Biblioteca UNAMAD')</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="@yield('meta_description', 'Biblioteca UNAMAD: consulta catalogos, revisa disponibilidad de libros y gestiona reservas y prestamos en linea.')">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link href="{{ asset('lib/select2/css/select2.css') }}" rel="stylesheet">
<link href="{{ asset('css/admin.css') }}" rel="stylesheet">

<style>
:root {
    --library-forest: #184d3b;
    --library-forest-deep: #0f3025;
    --library-leaf: #2f7a5d;
    --library-gold: #d8b15c;
    --library-cream: #f7f1e4;
    --library-ink: #1f2c27;
    --library-mist: #edf2ef;
    --library-card: rgba(255, 255, 255, 0.82);
    --library-border: rgba(24, 77, 59, 0.12);
    --library-shadow: 0 24px 60px rgba(18, 39, 31, 0.12);
    --sidebar-width: 280px;
}

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    min-height: 100vh;
    color: var(--library-ink);
    background:
        radial-gradient(circle at top left, rgba(216, 177, 92, 0.22), transparent 28%),
        radial-gradient(circle at bottom right, rgba(47, 122, 93, 0.2), transparent 24%),
        linear-gradient(180deg, #f8f4ea 0%, #eef3ef 48%, #e7efe9 100%);
    font-family: "Segoe UI", "Trebuchet MS", sans-serif;
}

.library-shell {
    min-height: 100vh;
}

.library-sidebar {
    position: fixed;
    inset: 0 auto 0 0;
    width: var(--sidebar-width);
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    background:
        linear-gradient(180deg, rgba(15, 48, 37, 0.98) 0%, rgba(24, 77, 59, 0.96) 52%, rgba(47, 122, 93, 0.94) 100%);
    color: #fff;
    box-shadow: 18px 0 50px rgba(10, 26, 20, 0.18);
    z-index: 1040;
    transition: transform 0.28s ease;
}

.library-brand {
    padding: 1.1rem 1.15rem;
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 1.4rem;
    background: rgba(255, 255, 255, 0.06);
    backdrop-filter: blur(10px);
}

.library-brand-header {
    display: flex;
    align-items: center;
    gap: 0.9rem;
    margin-bottom: 0.95rem;
}

.library-brand-mark {
    width: 64px;
    height: 64px;
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    border-radius: 18px;
    overflow: hidden;
    color: #fff;
    background:
        radial-gradient(circle at 30% 30%, rgba(255, 255, 255, 0.3), transparent 32%),
        linear-gradient(135deg, #efc96f 0%, #c98e1f 48%, #7e4d14 100%);
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.28),
        0 14px 28px rgba(0, 0, 0, 0.18);
}

.library-brand-mark::before {
    content: "";
    position: absolute;
    inset: 6px;
    border-radius: 14px;
    border: 1px solid rgba(255, 255, 255, 0.35);
}

.library-brand-seal {
    position: relative;
    z-index: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    line-height: 1;
}

.library-brand-seal strong {
    font-size: 1rem;
    letter-spacing: 0.14em;
}

.library-brand-seal span {
    margin-top: 0.25rem;
    font-size: 0.5rem;
    letter-spacing: 0.18em;
    opacity: 0.92;
}

.library-brand-copy {
    min-width: 0;
}

.library-brand-copy h1 {
    margin: 0;
    font-size: 1.08rem;
    font-weight: 800;
    letter-spacing: 0.05em;
}

.library-brand-copy small {
    display: block;
    margin-top: 0.18rem;
    color: rgba(255, 255, 255, 0.76);
    font-size: 0.72rem;
    line-height: 1.35;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.library-brand p {
    margin: 0;
    color: rgba(255, 255, 255, 0.72);
    font-size: 0.92rem;
    line-height: 1.5;
}

.library-nav {
    display: grid;
    gap: 0.5rem;
}

.library-nav-link {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    padding: 0.9rem 1rem;
    border-radius: 1rem;
    color: rgba(255, 255, 255, 0.85);
    text-decoration: none;
    transition: transform 0.2s ease, background-color 0.2s ease, color 0.2s ease;
}

.library-nav-link:focus-visible,
.library-login-btn:focus-visible,
.library-logout-btn:focus-visible,
.library-menu-btn:focus-visible {
    outline: 3px solid rgba(242, 207, 130, 0.9);
    outline-offset: 3px;
}

.library-nav-icon {
    width: 42px;
    height: 42px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    border-radius: 14px;
    font-size: 1.1rem;
    color: #fff;
    background: linear-gradient(135deg, rgba(216, 177, 92, 0.34), rgba(255, 255, 255, 0.08));
    border: 1px solid rgba(255, 255, 255, 0.14);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.18);
    transition: transform 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
}

.library-nav-link.nav-home .library-nav-icon {
    background: linear-gradient(135deg, rgba(72, 166, 120, 0.95), rgba(20, 86, 63, 0.95));
}

.library-nav-link.nav-catalog .library-nav-icon {
    background: linear-gradient(135deg, rgba(56, 126, 201, 0.95), rgba(28, 74, 135, 0.95));
}

.library-nav-link.nav-events .library-nav-icon {
    background: linear-gradient(135deg, rgba(223, 150, 63, 0.95), rgba(180, 90, 28, 0.95));
}

.library-nav-link.nav-reservations .library-nav-icon {
    background: linear-gradient(135deg, rgba(167, 92, 214, 0.95), rgba(108, 47, 149, 0.95));
}

.library-nav-link.nav-loans .library-nav-icon {
    background: linear-gradient(135deg, rgba(228, 91, 109, 0.95), rgba(151, 34, 61, 0.95));
}

.library-nav-text {
    display: flex;
    flex-direction: column;
    line-height: 1.15;
}

.library-nav-text strong {
    font-size: 0.95rem;
    font-weight: 700;
}

.library-nav-text small {
    color: rgba(255, 255, 255, 0.62);
    font-size: 0.76rem;
    margin-top: 0.15rem;
}

.library-nav-link:hover,
.library-nav-link.is-active {
    color: #fff;
    background: rgba(255, 255, 255, 0.12);
    transform: translateX(4px);
}

.library-nav-link:hover .library-nav-icon,
.library-nav-link.is-active .library-nav-icon {
    transform: scale(1.08);
    box-shadow:
        0 12px 24px rgba(8, 18, 14, 0.24),
        inset 0 1px 0 rgba(255, 255, 255, 0.22);
}

.library-nav-link.nav-home:hover .library-nav-icon,
.library-nav-link.nav-home.is-active .library-nav-icon {
    background: linear-gradient(135deg, #7fe0a8, #1d7f59);
}

.library-nav-link.nav-catalog:hover .library-nav-icon,
.library-nav-link.nav-catalog.is-active .library-nav-icon {
    background: linear-gradient(135deg, #74c0ff, #2f6fd4);
}

.library-nav-link.nav-events:hover .library-nav-icon,
.library-nav-link.nav-events.is-active .library-nav-icon {
    background: linear-gradient(135deg, #ffd37a, #d97a25);
}

.library-nav-link.nav-reservations:hover .library-nav-icon,
.library-nav-link.nav-reservations.is-active .library-nav-icon {
    background: linear-gradient(135deg, #d6a7ff, #8a47c7);
}

.library-nav-link.nav-loans:hover .library-nav-icon,
.library-nav-link.nav-loans.is-active .library-nav-icon {
    background: linear-gradient(135deg, #ff9aa9, #d64566);
}

.library-nav-link.is-active .library-nav-text small,
.library-nav-link:hover .library-nav-text small {
    color: rgba(255, 255, 255, 0.82);
}

.library-sidebar-footer {
    margin-top: auto;
    padding: 1rem 1.05rem;
    border-radius: 1.2rem;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.library-sidebar-footer small {
    color: rgba(255, 255, 255, 0.74);
}

.library-main {
    min-height: 100vh;
    margin-left: var(--sidebar-width);
    padding: 1.35rem;
}

.library-topbar {
    position: sticky;
    top: 0;
    z-index: 1030;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.35rem;
    padding: 0.95rem 1.15rem;
    border: 1px solid rgba(255, 255, 255, 0.55);
    border-radius: 1.4rem;
    background: rgba(255, 251, 244, 0.82);
    backdrop-filter: blur(14px);
    box-shadow: 0 10px 35px rgba(24, 77, 59, 0.08);
}

.library-topbar-title {
    display: flex;
    align-items: center;
    gap: 0.9rem;
    min-width: 0;
}

.library-topbar-title-badge {
    width: 44px;
    height: 44px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    border-radius: 14px;
    font-size: 1.1rem;
    color: #fff;
    background: linear-gradient(135deg, var(--library-leaf), var(--library-forest-deep));
    box-shadow: 0 10px 24px rgba(24, 77, 59, 0.22);
}

.library-topbar-title h5 {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 700;
}

.library-topbar-title span {
    display: block;
    color: #5a6d66;
    font-size: 0.9rem;
}

.library-topbar-actions {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.library-user-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.55rem 0.9rem;
    border-radius: 999px;
    background: rgba(24, 77, 59, 0.08);
    color: var(--library-forest);
    font-weight: 600;
}

.library-user-chip i {
    color: var(--library-gold);
}

.library-login-btn,
.library-logout-btn,
.library-menu-btn {
    border: 0;
    border-radius: 999px;
    font-weight: 600;
}

.library-login-btn {
    color: #16392d;
    background: linear-gradient(135deg, #efd08c, #f7e7bb);
}

.library-logout-btn {
    color: #fff;
    background: linear-gradient(135deg, #205842, #123529);
}

.library-menu-btn {
    width: 44px;
    height: 44px;
    display: none;
    align-items: center;
    justify-content: center;
    color: #fff;
    background: linear-gradient(135deg, var(--library-leaf), var(--library-forest-deep));
    box-shadow: 0 12px 30px rgba(24, 77, 59, 0.2);
}

.library-content {
    padding: 1.4rem;
    border: 1px solid rgba(255, 255, 255, 0.45);
    border-radius: 1.8rem;
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.72), rgba(255, 255, 255, 0.6));
    box-shadow: var(--library-shadow);
}

.library-footer {
    margin-top: 1.2rem;
    padding: 1.15rem 1.3rem;
    border-radius: 1.4rem;
    border: 1px solid rgba(255, 255, 255, 0.45);
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.72), rgba(249, 246, 238, 0.82));
    box-shadow: 0 12px 30px rgba(24, 77, 59, 0.07);
}

.library-footer-grid {
    display: grid;
    grid-template-columns: 1.5fr 1fr 1fr;
    gap: 1.1rem;
    align-items: start;
}

.library-footer h6 {
    margin-bottom: 0.55rem;
    color: #173d2f;
    font-weight: 800;
}

.library-footer p,
.library-footer small,
.library-footer a {
    color: #61746d;
}

.library-footer a {
    text-decoration: none;
}

.library-footer a:hover {
    color: #1f674d;
}

.library-footer-list {
    display: grid;
    gap: 0.45rem;
}

.library-footer-bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding-top: 0.95rem;
    margin-top: 0.95rem;
    border-top: 1px solid rgba(24, 77, 59, 0.1);
    color: #6a7b74;
    font-size: 0.88rem;
}

.hero,
.card,
.modal-content {
    border-radius: 1.25rem;
}

.card,
.table,
.modal-content {
    border-color: var(--library-border);
}

.card {
    background: var(--library-card);
    box-shadow: 0 14px 36px rgba(24, 77, 59, 0.08);
}

.book-card,
.card-hover {
    overflow: hidden;
    transition: transform 0.22s ease, box-shadow 0.22s ease;
}

.book-card:hover,
.card-hover:hover {
    transform: translateY(-6px);
    box-shadow: 0 18px 40px rgba(24, 77, 59, 0.16);
}

.libro-img {
    width: 100%;
    height: 300px;
    object-fit: contain;
    background: linear-gradient(180deg, #fbfaf5, #f2f4ef);
    padding: 12px;
    transition: transform 0.25s ease;
}

.book-card:hover .libro-img {
    transform: scale(1.04);
}

.btn-libro {
    color: #fff;
    border: 0;
    border-radius: 0.9rem;
    background: linear-gradient(135deg, var(--library-leaf), var(--library-forest-deep));
}

.btn-libro:hover {
    color: #fff;
    background: linear-gradient(135deg, #2c8564, #0d2f23);
}

.stars i {
    color: #d5a942 !important;
    font-size: 14px;
}

.rating {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-start;
}

.rating input {
    display: none;
}

.rating label {
    font-size: 25px;
    color: #ccc;
    cursor: pointer;
    transition: color 0.2s ease;
}

.rating input:checked ~ label,
.rating label:hover,
.rating label:hover ~ label {
    color: #ffc107;
}

.overlay {
    position: fixed;
    inset: 0;
    display: none;
    background: rgba(9, 21, 17, 0.4);
    backdrop-filter: blur(4px);
    z-index: 1035;
}

.overlay.active {
    display: block;
}

#mensaje_container {
    position: fixed;
    top: 1rem;
    right: 1rem;
    z-index: 1060;
}

@media (max-width: 991.98px) {
    .library-sidebar {
        transform: translateX(-100%);
    }

    .library-sidebar.active {
        transform: translateX(0);
    }

    .library-main {
        margin-left: 0;
        padding: 1rem;
    }

    .library-menu-btn {
        display: inline-flex;
    }

    .library-topbar {
        padding: 0.9rem 1rem;
    }

    .library-content {
        padding: 1rem;
        border-radius: 1.4rem;
    }
}

@media (max-width: 575.98px) {
    .library-topbar {
        align-items: flex-start;
    }

    .library-topbar-title {
        width: 100%;
    }

    .library-topbar-actions {
        width: 100%;
        justify-content: space-between;
    }
}

@media (max-width: 991.98px) {
    .library-footer-grid {
        grid-template-columns: 1fr;
    }

    .library-footer-bottom {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
</head>

<body>
@php($user = Auth::user())

<a href="#contenido-principal" class="visually-hidden-focusable position-absolute top-0 start-0 m-3 p-2 rounded bg-white text-dark">
    Saltar al contenido principal
</a>

<div class="library-shell">
    <aside class="library-sidebar" id="sidebar" aria-label="Menu principal">
        <div class="library-brand">
            <div class="library-brand-header">
                <div class="library-brand-mark">
                    <div class="library-brand-seal">
                        <strong>UNA</strong>
                        <span>MDD</span>
                    </div>
                </div>
                <div class="library-brand-copy">
                    <h1>Biblioteca UNAMAD</h1>
                    <small>Universidad Nacional Amazonica de Madre de Dios</small>
                </div>
            </div>
            <p>Explora catálogos, reservas y préstamos desde una experiencia más clara y ordenada.</p>
        </div>

        <nav class="library-nav" aria-label="Navegacion principal">
            <a href="{{ route('home') }}" class="library-nav-link nav-home {{ request()->routeIs('home') ? 'is-active' : '' }}">
                <span class="library-nav-icon">
                    <i class="bi bi-house-heart-fill"></i>
                </span>
                <span class="library-nav-text">
                    <strong>Inicio</strong>
                    <small>Portada principal</small>
                </span>
            </a>
            <a href="{{ route('catalogo') }}" class="library-nav-link nav-catalog {{ request()->routeIs('catalogo') ? 'is-active' : '' }}">
                <span class="library-nav-icon">
                    <i class="bi bi-collection-fill"></i>
                </span>
                <span class="library-nav-text">
                    <strong>Catalogo</strong>
                    <small>Busqueda de libros</small>
                </span>
            </a>
            <a href="{{ route('evento') }}" class="library-nav-link nav-events {{ request()->routeIs('evento') ? 'is-active' : '' }}">
                <span class="library-nav-icon">
                    <i class="bi bi-stars"></i>
                </span>
                <span class="library-nav-text">
                    <strong>Eventos</strong>
                    <small>Novedades y agenda</small>
                </span>
            </a>
            @auth
            <a href="{{ route('mis.reservas') }}" class="library-nav-link nav-reservations {{ request()->routeIs('mis.reservas') ? 'is-active' : '' }}">
                <span class="library-nav-icon">
                    <i class="bi bi-bookmark-star-fill"></i>
                </span>
                <span class="library-nav-text">
                    <strong>Mis Reservas</strong>
                    <small>Solicitudes activas</small>
                </span>
            </a>
            <a href="{{ route('prestamos') }}" class="library-nav-link nav-loans {{ request()->routeIs('prestamos') ? 'is-active' : '' }}">
                <span class="library-nav-icon">
                    <i class="bi bi-arrow-left-right"></i>
                </span>
                <span class="library-nav-text">
                    <strong>Prestamos</strong>
                    <small>Control de movimientos</small>
                </span>
            </a>
            @endauth
        </nav>

        <div class="library-sidebar-footer">
            <strong class="d-block mb-1">Ambiente de lectura</strong>
            <small>Un espacio pensado para consultar libros, revisar disponibilidad y gestionar movimientos sin perder contexto.</small>
        </div>
    </aside>

    <div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

    <main class="library-main" id="contenido-principal">
        <header class="library-topbar">
            <div class="library-topbar-title">
                <button type="button" class="library-menu-btn" onclick="toggleSidebar()">
                    <i class="bi bi-list"></i>
                </button>
                <div class="library-topbar-title-badge">
                    <i class="bi bi-buildings-fill"></i>
                </div>
                <div>
                    <h5>Sistema de Biblioteca</h5>
                    <span>Navegacion central para catalogo, reservas y consultas</span>
                </div>
            </div>

            <div class="library-topbar-actions">
                @auth
                <span class="library-user-chip">
                    <i class="bi bi-person-circle"></i>
                    {{ $user->name }}
                </span>

                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn library-logout-btn">
                        <i class="bi bi-box-arrow-right me-1"></i>
                        Salir
                    </button>
                </form>
                @else
                <a href="{{ route('login') }}" class="btn library-login-btn">
                    <i class="bi bi-box-arrow-in-right me-1"></i>
                    Iniciar sesion
                </a>
                @endauth
            </div>
        </header>

        <section class="library-content">
            @yield('content')
        </section>

        <footer class="library-footer" aria-label="Pie de pagina institucional">
            <div class="library-footer-grid">
                <div>
                    <h6>Biblioteca UNAMAD</h6>
                    <p class="mb-2">
                        Plataforma de consulta para explorar catalogos, revisar disponibilidad bibliografica
                        y gestionar reservas y prestamos en la Universidad Nacional Amazonica de Madre de Dios.
                    </p>
                    <small>Madre de Dios, Peru</small>
                </div>

                <div>
                    <h6>Enlaces rapidos</h6>
                    <div class="library-footer-list">
                        <a href="{{ route('home') }}">Inicio</a>
                        <a href="{{ route('catalogo') }}">Catalogo</a>
                        <a href="{{ route('evento') }}">Eventos</a>
                        @auth
                        <a href="{{ route('mis.reservas') }}">Mis reservas</a>
                        @endauth
                    </div>
                </div>

                <div>
                    <h6>Contacto</h6>
                    <div class="library-footer-list">
                        <span><i class="bi bi-geo-alt me-2"></i>Universidad Nacional Amazonica de Madre de Dios</span>
                        <span><i class="bi bi-envelope me-2"></i>biblioteca@unamad.edu.pe</span>
                        <span><i class="bi bi-clock me-2"></i>Consulta digital disponible todo el dia</span>
                    </div>
                </div>
            </div>

            <div class="library-footer-bottom">
                <span>&copy; {{ now()->year }} Biblioteca UNAMAD. Todos los derechos reservados.</span>
                <span>Diseñado para una experiencia de consulta clara, accesible e institucional.</span>
            </div>
        </footer>
    </main>
</div>

<div id="mensaje_container"></div>

@yield('modal')

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');

    sidebar.classList.toggle('active');
    overlay.classList.toggle('active');
}
</script>
<script src="{{ asset('js/jquery-3.6.3.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('/lib/select2/js/select2.js') }}"></script>
<script src="{{ asset('/lib/select2/js/i18n/es.js') }}"></script>
<script src="{{ asset('js/admin.js') }}"></script>
@yield('js')

</body>
</html>
