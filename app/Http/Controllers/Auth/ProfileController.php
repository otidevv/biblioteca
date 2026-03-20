<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    //
    public function edit()
    {
        // Aquí puedes cargar los datos del usuario autenticado
        return view('auth.perfil');
    }
}
