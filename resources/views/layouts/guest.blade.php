<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Sistema de Biblioteca') }}</title>

        <link rel="icon" type="image/png" href="{{ asset('img/logo_unamad.png') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-slate-800">
        <div class="relative min-h-screen overflow-hidden bg-[linear-gradient(135deg,_#f6f1df_0%,_#eef5ef_45%,_#dfeae3_100%)]">
            <div class="pointer-events-none absolute inset-0">
                <div class="absolute -left-24 top-0 h-72 w-72 rounded-full bg-emerald-900/10 blur-3xl"></div>
                <div class="absolute right-0 top-16 h-80 w-80 rounded-full bg-amber-400/20 blur-3xl"></div>
                <div class="absolute bottom-0 left-1/3 h-72 w-72 rounded-full bg-emerald-700/10 blur-3xl"></div>
            </div>

            <div class="relative mx-auto flex min-h-screen w-full max-w-7xl items-center px-4 py-8 sm:px-6 lg:px-8">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
