<?php

namespace App\Providers;

use App\Services\CentroNotificacionesService;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Campanas de avisos. Se habian perdido en el commit 7c72363 al agregar el correo de
        // recuperacion de contrasena, y desde entonces ambas campanas salian vacias.
        View::composer('layouts.biblioteca', function ($view) {
            $view->with('libraryAlerts', app(CentroNotificacionesService::class)->paraCampanaBiblioteca(Auth::user(), 6));
        });

        View::composer('layouts.admin', function ($view) {
            $user = Auth::user();
            $view->with('adminAlerts', $user
                ? app(CentroNotificacionesService::class)->obtenerParaUsuario($user, 6)->values()
                : collect());
        });

        ResetPassword::toMailUsing(function ($notifiable, $token) {

            $url = route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]);

            return (new MailMessage)
                ->subject('Recuperación de contraseña - Sistema de Biblioteca UNAMAD')
                ->view('emails.reset_password', [
                    'user' => $notifiable,
                    'url' => $url,
                ]);
        });
    }
}
