<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carrera extends Model
{
    //
    protected $fillable = [
        'nombre',
        'codigo',
        'descripcion',
        'activo'
    ];

    public function personas()
    {
        return $this->hasMany(Persona::class);
    }
}
