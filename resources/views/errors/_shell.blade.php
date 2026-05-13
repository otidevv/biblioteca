{{--
    Variables esperadas:
    $code       - "403" | "404" | "500" ...
    $gradient   - clases Tailwind para el gradiente del header
    $glowColor  - color rgba para el glow del header
    $icon       - clase Bootstrap Icon (ej. "bi-shield-lock-fill")
    $eyebrowBg  - clases Tailwind bg para el eyebrow
    $eyebrowTxt - clases Tailwind text para el eyebrow
    $eyebrow    - texto del eyebrow
    $title      - título principal
    $copy       - descripción
    $actions    - array de [href, label, icon, primary]
    $support    - bool: mostrar bloque de soporte OTI
--}}

<div class="flex items-center justify-center min-h-[calc(100vh-140px)] p-6">
    <div class="w-full max-w-lg">

        {{-- ── Tarjeta principal ── --}}
        <div class="rounded-3xl overflow-hidden
                    bg-white border border-slate-200/70
                    shadow-[0_4px_6px_rgba(15,23,42,0.04),_0_20px_40px_rgba(15,23,42,0.08)]
                    dark:bg-slate-900 dark:border-slate-700/60
                    dark:shadow-[0_4px_6px_rgba(2,6,23,0.2),_0_20px_40px_rgba(2,6,23,0.35)]">

            {{-- ── Header con gradiente ── --}}
            <div class="relative flex items-center justify-center overflow-hidden h-56
                        bg-gradient-to-br {{ $gradient }}">

                {{-- Glow central --}}
                <div class="absolute inset-0"
                     style="background: radial-gradient(circle at 50% 60%, {{ $glowColor }}, transparent 65%)">
                </div>

                {{-- Círculos decorativos --}}
                <div class="absolute -bottom-16 -left-10 w-52 h-52 rounded-full bg-white/[0.05]"></div>
                <div class="absolute -top-8 right-8 w-36 h-36 rounded-full bg-white/[0.04]"></div>
                <div class="absolute top-8 left-12 w-20 h-20 rounded-full border-2 border-white/10"></div>
                <div class="absolute bottom-8 right-16 w-10 h-10 rounded-full border-2 border-white/[0.08]"></div>

                {{-- Código de fondo --}}
                <span class="absolute select-none pointer-events-none
                             text-[110px] font-black leading-none tracking-tighter
                             text-white/[0.09]">
                    {{ $code }}
                </span>

                {{-- Icono central --}}
                <div class="relative z-10
                            w-20 h-20 rounded-2xl
                            bg-white/[0.14] border border-white/20
                            backdrop-blur-sm
                            flex items-center justify-center
                            shadow-[0_12px_28px_rgba(15,23,42,0.22)]">
                    <i class="bi {{ $icon }} text-white text-4xl leading-none"></i>
                </div>
            </div>

            {{-- ── Cuerpo ── --}}
            <div class="px-10 py-9 text-center">

                {{-- Eyebrow --}}
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full
                             text-[11px] font-extrabold uppercase tracking-widest
                             {{ $eyebrowBg }} {{ $eyebrowTxt }} mb-4">
                    <i class="bi bi-exclamation-triangle-fill text-[10px]"></i>
                    {{ $eyebrow }}
                </span>

                {{-- Título --}}
                <h1 class="text-[1.75rem] font-extrabold leading-tight mb-3
                           text-slate-900 dark:text-slate-50">
                    {{ $title }}
                </h1>

                {{-- Descripción --}}
                <p class="text-sm leading-relaxed mb-7
                          text-slate-500 dark:text-slate-400">
                    {!! $copy !!}
                </p>

                {{-- Botones --}}
                <div class="flex flex-col items-center gap-2.5">
                    @foreach ($actions as $action)
                        @if ($action['primary'])
                            <a href="{{ $action['href'] }}"
                               class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl
                                      bg-gradient-to-r from-teal-700 to-blue-700
                                      text-white text-sm font-bold
                                      shadow-[0_4px_14px_rgba(15,118,110,0.3)]
                                      hover:opacity-90 hover:-translate-y-0.5
                                      transition-all duration-150
                                      no-underline hover:no-underline hover:text-white">
                                <i class="bi {{ $action['icon'] }}"></i>
                                {{ $action['label'] }}
                            </a>
                        @else
                            <a href="{{ $action['href'] }}"
                               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg
                                      text-sm font-semibold
                                      text-slate-500 hover:text-slate-700
                                      hover:bg-slate-100
                                      dark:text-slate-400 dark:hover:text-slate-200
                                      dark:hover:bg-slate-800
                                      transition-all duration-150
                                      no-underline hover:no-underline">
                                <i class="bi {{ $action['icon'] }}"></i>
                                {{ $action['label'] }}
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ── Bloque de soporte OTI (solo en 500 / 503) ── --}}
        @if (!empty($support))
        <div class="mt-4 rounded-2xl px-6 py-5
                    bg-slate-50 border border-slate-200/70
                    dark:bg-slate-800/60 dark:border-slate-700/50">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-10 h-10 rounded-xl
                            bg-teal-50 dark:bg-teal-900/40
                            flex items-center justify-center">
                    <i class="bi bi-headset text-teal-700 dark:text-teal-400 text-lg"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[13px] font-extrabold uppercase tracking-wider mb-1
                              text-slate-700 dark:text-slate-300">
                        ¿Necesitas ayuda?
                    </p>
                    <p class="text-[13px] leading-snug
                              text-slate-500 dark:text-slate-400">
                        Consulta con el soporte de
                        <span class="font-bold text-teal-700 dark:text-teal-400">OTI</span>
                        — Oficina de Tecnologías de la Información.
                    </p>
                    <div class="mt-2.5 flex flex-wrap gap-2">
                        <a href="mailto:oti@unamad.edu.pe"
                           class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg
                                  text-[12px] font-semibold
                                  bg-teal-50 text-teal-700 border border-teal-200/60
                                  dark:bg-teal-900/30 dark:text-teal-400 dark:border-teal-700/40
                                  hover:bg-teal-100 dark:hover:bg-teal-900/50
                                  transition-colors duration-150
                                  no-underline hover:no-underline">
                            <i class="bi bi-envelope-fill text-[11px]"></i>
                            oti@unamad.edu.pe
                        </a>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg
                                     text-[12px] font-semibold
                                     bg-slate-100 text-slate-600 border border-slate-200/60
                                     dark:bg-slate-700/50 dark:text-slate-400 dark:border-slate-600/40">
                            <i class="bi bi-clock text-[11px]"></i>
                            Lun–Vie 8:00–17:00
                        </span>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>
