<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sistema Biblioteca</title>

    <link href="{{ asset('lib/tabler/css/tabler.min.css') }}" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @yield('css')
</head>

<body
    x-data="{
        sidebarMobile: false,
        sidebarCollapsed: false,
        windowWidth: window.innerWidth
    }"
    x-init="
        window.addEventListener('resize', () => windowWidth = window.innerWidth)
    "
    class="bg-gray-100"
>

<!-- OVERLAY MOBILE -->
<div
    x-show="sidebarMobile && windowWidth < 768"
    @click="sidebarMobile = false"
    class="fixed inset-0 bg-black/50 z-30 md:hidden"
    x-transition
></div>

<div class="flex min-h-screen">

    <!-- SIDEBAR -->
    <aside
        class="fixed top-0 left-0 z-40 h-screen
               bg-gradient-to-b from-emerald-600 to-cyan-700
               text-white shadow-2xl overflow-y-auto
               transition-all duration-300 ease-in-out
               md:relative"
        :class="{
            /* MOBILE */
            '-translate-x-full w-72': !sidebarMobile && windowWidth < 768,
            'translate-x-0 w-72': sidebarMobile && windowWidth < 768,

            /* DESKTOP */
            'w-72': windowWidth >= 768 && !sidebarCollapsed,
            'w-20': windowWidth >= 768 && sidebarCollapsed
        }"
    >

        <!-- HEADER -->
        <div class="flex items-center justify-between p-4">
            <span
                x-show="!sidebarCollapsed || windowWidth < 768"
                class="text-lg font-bold"
            >
                Biblioteca
            </span>

            <button
                @click="
                    windowWidth < 768
                        ? sidebarMobile = !sidebarMobile
                        : sidebarCollapsed = !sidebarCollapsed
                "
                class="p-2 rounded-lg hover:bg-white/20"
            >
                ☰
            </button>
        </div>

        <!-- MENU -->
        <nav class="px-2 space-y-2">
            @foreach ($permisosUsuario as $permiso)
                <div x-data="{ open: false }">
                    <button
                        @click="open = !open"
                        class="w-full flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/20"
                    >
                        {!! $permiso['icono'] ?? '⚙️' !!}
                        <span x-show="!sidebarCollapsed || windowWidth < 768">
                            {{ $permiso['nombre'] }}
                        </span>
                    </button>

                    @if (!empty($permiso['subpermisos']))
                        <div
                            x-show="open && (!sidebarCollapsed || windowWidth < 768)"
                            x-transition
                            class="ml-8 mt-1 space-y-1"
                        >
                            @foreach ($permiso['subpermisos'] as $subpermiso)
                                <a
                                    href="{{ url($subpermiso['ruta']) }}"
                                    class="block px-2 py-1 text-sm hover:underline"
                                >
                                    {{ $subpermiso['nombre'] }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </nav>
    </aside>

    <!-- CONTENIDO -->
    <main
    class="flex-1 p-6 transition-all duration-300"
    :class="{
        'ml-0': windowWidth < 768,
        'ml-30': windowWidth >= 768 && !sidebarCollapsed,

        'ml-20': windowWidth >= 768 && sidebarCollapsed
    }"
>

        <!-- BOTÓN MOBILE -->
        <div class="md:hidden mb-4">
            <button
                @click="sidebarMobile = true"
                class="px-4 py-2 bg-emerald-600 text-white rounded-lg shadow"
            >
                ☰ Menú
            </button>
        </div>

        

        @yield('content')
    </main>

</div>

<!-- ALERTAS -->
<div id="mensaje_container" class="fixed top-4 right-4 z-50 space-y-2"></div>

<!-- MODALES -->
@yield('modal')

<!-- SCRIPTS -->
<script src="https://unpkg.com/heroicons@2.0.18/24/outline/index.js" defer></script>
<script src="{{ asset('js/jquery-3.6.3.min.js') }}"></script>
<script src="{{ asset('lib/tabler/js/tabler.js') }}"></script>
<script src="{{ asset('js/admin.js') }}"></script>

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
