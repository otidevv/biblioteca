<?php

namespace App\Http\Responses;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): Response
    {
        $user = Auth::user();

        // 👉 REDIRECCIÓN A ADMINISTRACIÓN
        return redirect()->route('administracion.index');
    }
}
