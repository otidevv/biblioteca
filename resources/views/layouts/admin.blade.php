<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Panel de Biblioteca</title>

    <link href="{{ asset('lib/tabler/css/tabler.min.css') }}" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="{{ asset('css/admin.css') }}?v={{ filemtime(public_path('css/admin.css')) }}" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @yield('css')
</head>
<body
    x-data="{
        sidebarMobile: false,
        sidebarCollapsed: false,
        windowWidth: window.innerWidth,
        darkMode: localStorage.getItem('admin-dark-mode') === 'true'
    }"
    x-init="
        window.addEventListener('resize', () => windowWidth = window.innerWidth);
        $watch('darkMode', value => {
            document.body.classList.toggle('admin-dark', value);
            document.body.classList.toggle('dark-mode', value);
            localStorage.setItem('admin-dark-mode', value ? 'true' : 'false');
        });
        document.body.classList.toggle('admin-dark', darkMode);
        document.body.classList.toggle('dark-mode', darkMode);
    "
    class="admin-shell"
>
<script>
    window.permisosUsuario = @json($permisosUsuario ?? []);
</script>

<div
    x-show="sidebarMobile && windowWidth < 1024"
    @click="sidebarMobile = false"
    class="admin-overlay"
    x-transition.opacity
></div>

<div class="admin-app">
    <aside
        class="admin-sidebar"
        :class="{
            'is-open': sidebarMobile && windowWidth < 1024,
            'is-collapsed': sidebarCollapsed && windowWidth >= 1024
        }"
    >
        <div class="admin-sidebar__brand">
            <a href="{{ route('dashboard') }}" class="admin-brand">
                <img src="{{ asset('img/logo_unamad.png') }}" alt="UNAMAD" class="admin-brand__logo">
                <div x-show="!sidebarCollapsed || windowWidth < 1024" x-transition.opacity>
                    <div class="admin-brand__kicker">Universidad Nacional Amazonica de Madre de Dios</div>
                    <div class="admin-brand__title">Sistema de Biblioteca</div>
                </div>
            </a>

            <button
                @click="
                    windowWidth < 1024
                        ? sidebarMobile = !sidebarMobile
                        : sidebarCollapsed = !sidebarCollapsed
                "
                type="button"
                class="admin-sidebar__toggle"
                aria-label="Alternar navegacion"
            >
                <span class="admin-sidebar__toggle-line"></span>
                <span class="admin-sidebar__toggle-line"></span>
                <span class="admin-sidebar__toggle-line"></span>
            </button>
        </div>

        <div class="admin-sidebar__meta" x-show="!sidebarCollapsed || windowWidth < 1024" x-transition.opacity>
            <div class="admin-sidebar__meta-label">Panel administrativo</div>
            <div class="admin-sidebar__meta-value">{{ auth()->user()->name ?? 'Usuario' }}</div>
        </div>

        <nav class="admin-nav">
            @foreach ($permisosUsuario as $permiso)
                @php
                    $subpermisos = $permiso['subpermisos'] ?? [];
                    $hasChildren = !empty($subpermisos);
                    $isActiveGroup = collect($subpermisos)->contains(fn ($subpermiso) => request()->is(ltrim($subpermiso['ruta'] ?? '', '/')));
                @endphp

                <div x-data="{ open: {{ $isActiveGroup ? 'true' : 'false' }} }" class="admin-nav__group">
                    @if ($hasChildren)
                        <button
                            type="button"
                            @click="open = !open"
                            class="admin-nav__button"
                            :class="{ 'is-active': open }"
                        >
                            <span class="admin-nav__icon">{!! $permiso['icono'] ?? '&#9881;' !!}</span>
                            <span class="admin-nav__label" x-show="!sidebarCollapsed || windowWidth < 1024" x-transition.opacity>
                                {{ $permiso['nombre'] }}
                            </span>
                            <span class="admin-nav__caret" x-show="!sidebarCollapsed || windowWidth < 1024" x-transition.opacity>
                                <span x-text="open ? '-' : '+'"></span>
                            </span>
                        </button>

                        <div
                            x-show="open && (!sidebarCollapsed || windowWidth < 1024)"
                            x-transition
                            class="admin-nav__submenu"
                        >
                            @foreach ($subpermisos as $subpermiso)
                                <a
                                    href="{{ url($subpermiso['ruta']) }}"
                                    class="admin-nav__link {{ request()->is(ltrim($subpermiso['ruta'], '/')) ? 'is-current' : '' }}"
                                >
                                    {{ $subpermiso['nombre'] }}
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="admin-nav__button admin-nav__button--parent" role="presentation">
                            <span class="admin-nav__icon">{!! $permiso['icono'] ?? '&#9881;' !!}</span>
                            <span class="admin-nav__label" x-show="!sidebarCollapsed || windowWidth < 1024" x-transition.opacity>
                                {{ $permiso['nombre'] }}
                            </span>
                        </div>
                    @endif
                </div>
            @endforeach
        </nav>
    </aside>

    <main class="admin-main" :class="{ 'is-collapsed': sidebarCollapsed && windowWidth >= 1024 }">
        <header class="admin-topbar">
            <div class="admin-topbar__left">
                <button
                    @click="sidebarMobile = true"
                    type="button"
                    class="admin-topbar__menu d-lg-none"
                    aria-label="Abrir navegacion"
                >
                    <span></span>
                    <span></span>
                    <span></span>
                </button>

                <div>
                    <div class="admin-topbar__kicker">Gestion institucional</div>
                    <h1 class="admin-topbar__title">@yield('page-title', 'Panel de administracion')</h1>
                </div>
            </div>

            <div class="admin-topbar__right">
                <div class="admin-topbar__dropdown">
                    <button type="button" class="admin-icon-btn" @click="darkMode = !darkMode" :aria-pressed="darkMode">
                        <span x-show="!darkMode">☾</span>
                        <span x-show="darkMode">☀</span>
                    </button>
                </div>

                <div x-data="{ open: false }" class="admin-topbar__dropdown">
                    <button type="button" class="admin-icon-btn admin-icon-btn--bell" @click="open = !open">
                        <span>🔔</span>
                        @if(($adminAlerts ?? collect())->isNotEmpty())
                            <span class="admin-icon-btn__badge">{{ min(($adminAlerts ?? collect())->count(), 9) }}</span>
                        @endif
                    </button>

                    <div x-show="open" @click.outside="open = false" x-transition class="admin-dropdown admin-dropdown--alerts">
                        <div class="admin-dropdown__header">
                            <div>
                                <div class="admin-dropdown__eyebrow">Centro de mensajes</div>
                                <div class="admin-dropdown__title">Avisos para administracion</div>
                            </div>
                        </div>

                        @forelse(($adminAlerts ?? collect()) as $alert)
                            <a href="{{ $alert->url }}" class="admin-alert">
                                <span class="admin-alert__icon">
                                    @php $alertIcon = $alert->icono ?? 'bi-bell-fill'; @endphp
                                    @if(is_string($alertIcon) && str_starts_with($alertIcon, 'bi-'))
                                        <i class="bi {{ $alertIcon }}"></i>
                                    @else
                                        {{ $alertIcon }}
                                    @endif
                                </span>
                                <span>
                                    <span class="admin-alert__title">{{ $alert->titulo }}</span>
                                    <span class="admin-alert__copy">{{ $alert->contenido }}</span>
                                    <span class="admin-alert__meta">{{ $alert->meta }}</span>
                                </span>
                            </a>
                        @empty
                            <div class="admin-dropdown__empty">No hay mensajes o actualizaciones para mostrar.</div>
                        @endforelse
                    </div>
                </div>

                <div x-data="{ open: false }" class="admin-topbar__dropdown">
                    <button type="button" class="admin-user" @click="open = !open">
                        <span class="admin-user__avatar">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</span>
                        <span class="admin-user__meta">
                            <span class="admin-user__label">Sesión activa</span>
                            <span class="admin-user__name">{{ auth()->user()->name ?? 'Usuario' }}</span>
                        </span>
                    </button>

                    <div x-show="open" @click.outside="open = false" x-transition class="admin-dropdown admin-dropdown--user">
                        <div class="admin-dropdown__header">
                            <div class="admin-dropdown__title">{{ auth()->user()->name ?? 'Usuario' }}</div>
                            <div class="admin-dropdown__meta">{{ auth()->user()->email ?? '' }}</div>
                        </div>

                        <a href="{{ route('perfil.edit', ['layout' => 'admin']) }}" class="admin-dropdown__link">Ir al perfil</a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="admin-dropdown__link admin-dropdown__link--danger">Cerrar sesión</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <section class="admin-content">
            @yield('content')
        </section>
    </main>
</div>

<div id="mensaje_container" class="fixed top-4 right-4 z-50 space-y-2"></div>

@yield('modal')

<script src="{{ asset('js/jquery-3.6.3.min.js') }}"></script>
<script src="{{ asset('lib/tabler/js/tabler.js') }}"></script>
<script src="{{ asset('js/admin.js') }}?v={{ filemtime(public_path('js/admin.js')) }}"></script>
<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    });
</script>
@yield('js')
</body>
</html>
