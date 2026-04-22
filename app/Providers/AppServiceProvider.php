<?php

namespace App\Providers;

use App\Models\Actividad;
use App\Models\AvisoBiblioteca;
use App\Services\CentroNotificacionesService;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
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
