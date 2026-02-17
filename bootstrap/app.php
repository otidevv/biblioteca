<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CargarPermisosUsuario;
use App\Http\Middleware\PermisoPorRuta;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware): void {

        // ✅ Middleware global para vistas (menú)
        $middleware->web(append: [
            CargarPermisosUsuario::class,
        ]);

        // ✅ Alias de middleware (reemplaza Kernel.php)
        $middleware->alias([
            'permiso.ruta' => PermisoPorRuta::class,
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
