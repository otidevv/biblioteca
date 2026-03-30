<x-guest-layout>
    <div class="grid w-full gap-6 lg:grid-cols-[minmax(0,1.05fr)_minmax(420px,520px)] lg:items-stretch">
        <section class="relative hidden overflow-hidden rounded-[2rem] border border-emerald-950/10 bg-[linear-gradient(145deg,_rgba(16,52,40,0.96),_rgba(28,92,68,0.92))] p-8 text-white shadow-[0_30px_80px_rgba(16,52,40,0.22)] lg:flex lg:flex-col lg:justify-between">
            <div class="absolute inset-0 opacity-20">
                <div class="absolute left-10 top-10 h-40 w-40 rounded-full bg-amber-300 blur-3xl"></div>
                <div class="absolute bottom-10 right-10 h-52 w-52 rounded-full bg-emerald-300 blur-3xl"></div>
            </div>

            <div class="relative">
                <div class="inline-flex items-center gap-3 rounded-full border border-white/15 bg-white/10 px-4 py-2 text-sm font-semibold tracking-[0.18em] text-emerald-50 uppercase">
                    <x-application-logo class="h-9 w-9 rounded-full bg-white object-contain p-1.5" />
                    UNAMAD
                </div>

                <div class="mt-8 max-w-xl">
                    <p class="text-sm font-semibold uppercase tracking-[0.22em] text-amber-200/90">Sistema de Biblioteca</p>
                    <h1 class="mt-4 text-4xl font-extrabold leading-tight">
                        Acceso institucional para la comunidad lectora.
                    </h1>
                    <p class="mt-5 text-base leading-8 text-emerald-50/85">
                        Gestiona prestamos, revisa disponibilidad por sede, comenta tus lecturas y mantente conectado con el catalogo bibliografico de UNAMAD desde una sola plataforma.
                    </p>
                </div>
            </div>

            <div class="relative grid gap-4 sm:grid-cols-3">
                <div class="rounded-2xl border border-white/12 bg-white/10 p-4 backdrop-blur">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-200/90">Catalogo</p>
                    <p class="mt-2 text-sm leading-6 text-emerald-50/85">Consulta libros, autores y materiales relacionados desde cualquier dispositivo.</p>
                </div>
                <div class="rounded-2xl border border-white/12 bg-white/10 p-4 backdrop-blur">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-200/90">Prestamos</p>
                    <p class="mt-2 text-sm leading-6 text-emerald-50/85">Solicita ejemplares disponibles y administra tus reservas en tiempo real.</p>
                </div>
                <div class="rounded-2xl border border-white/12 bg-white/10 p-4 backdrop-blur">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-200/90">Resenas</p>
                    <p class="mt-2 text-sm leading-6 text-emerald-50/85">Comparte valoraciones y ayuda a otros lectores a descubrir mejores opciones.</p>
                </div>
            </div>
        </section>

        <section class="w-full rounded-[2rem] border border-white/70 bg-white/90 p-6 shadow-[0_24px_60px_rgba(25,61,47,0.12)] backdrop-blur sm:p-8 lg:p-10">
            <div class="mx-auto max-w-md">
                <div class="mb-8 flex items-center gap-4 lg:hidden">
                    <x-application-logo class="h-14 w-14 rounded-2xl bg-white object-contain p-2 shadow-sm ring-1 ring-emerald-900/10" />
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-800">UNAMAD</p>
                        <h1 class="text-xl font-extrabold text-slate-900">Sistema de Biblioteca</h1>
                    </div>
                </div>

                <div class="hidden lg:block">
                    <div class="inline-flex items-center gap-3 rounded-full bg-emerald-900/5 px-4 py-2 text-sm font-semibold text-emerald-900">
                        <x-application-logo class="h-10 w-10 rounded-full bg-white object-contain p-1.5 shadow-sm ring-1 ring-emerald-900/10" />
                        Acceso seguro
                    </div>
                </div>

                <div class="mt-6">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-amber-700">Bienvenido</p>
                    <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-slate-900">Inicia sesion en tu cuenta</h2>
                    <p class="mt-3 text-sm leading-7 text-slate-600">
                        Ingresa con tus credenciales institucionales para continuar en el sistema de biblioteca.
                    </p>
                </div>

                <x-auth-session-status class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5">
                    @csrf

                    @if (request('redirect'))
                        <input type="hidden" name="redirect" value="{{ request('redirect') }}">
                    @endif

                    <div>
                        <x-input-label for="email" :value="'Correo electronico'" class="mb-2 !text-sm !font-semibold !text-slate-700" />
                        <x-text-input
                            id="email"
                            class="block w-full rounded-2xl border-slate-200 bg-slate-50/70 px-4 py-3 !shadow-none focus:border-emerald-600 focus:bg-white focus:ring-emerald-600"
                            type="email"
                            name="email"
                            :value="old('email')"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="ejemplo@correo.com" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <x-input-label for="password" :value="'Contrasena'" class="!text-sm !font-semibold !text-slate-700" />
                            @if (Route::has('password.request'))
                                <a class="text-sm font-medium text-emerald-800 transition hover:text-emerald-600" href="{{ route('password.request') }}">
                                    Recuperar acceso
                                </a>
                            @endif
                        </div>

                        <x-text-input
                            id="password"
                            class="block w-full rounded-2xl border-slate-200 bg-slate-50/70 px-4 py-3 !shadow-none focus:border-emerald-600 focus:bg-white focus:ring-emerald-600"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="Ingresa tu contrasena" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <label for="remember_me" class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50/80 px-4 py-3 text-sm text-slate-600">
                        <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-emerald-700 shadow-sm focus:ring-emerald-600" name="remember">
                        <span>Mantener sesion iniciada en este dispositivo</span>
                    </label>

                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-[linear-gradient(135deg,_#1f6b4f,_#103428)] px-5 py-3.5 text-sm font-bold tracking-wide text-white shadow-[0_18px_40px_rgba(16,52,40,0.18)] transition hover:-translate-y-0.5 hover:shadow-[0_22px_44px_rgba(16,52,40,0.22)]">
                        Iniciar sesion
                    </button>
                </form>
            </div>
        </section>
    </div>
</x-guest-layout>
