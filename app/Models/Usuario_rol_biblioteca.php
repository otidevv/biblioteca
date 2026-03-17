<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usuario_rol_biblioteca extends Model
{
    //
    protected $fillable = [
        'user_id',
        'rol_id',
        'biblioteca_id',
        'activo',        
    ];       
}
