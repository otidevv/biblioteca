<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CargarPermisosUsuario;
use App\Http\Middleware\PermisoPorRuta;
use App\Http\Middleware\RegistrarVisita;
use App\Http\Middleware\RejectLectorDashboard;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
            'permiso.ruta'        => PermisoPorRuta::class,
            'no.lector.dashboard' => RejectLectorDashboard::class,
            'registrar.visita'    => RegistrarVisita::class,
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (HttpException $e, $request) {
            if ($request->expectsJson()) return null;

            $status = $e->getStatusCode();
            $views  = [403, 404, 419, 429, 500, 503];

            if (in_array($status, $views) && view()->exists("errors.{$status}")) {
                return response()->view("errors.{$status}", ['exception' => $e], $status);
            }
        });
    })
    ->create();
