<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Biblioteca extends Model
{
    //
    protected $fillable = [
        'codigo',
        'nombre',
        'direccion',
        'descripcion',
        'estado',
        'imagen'
    ];
    public function ejemplares(){
        return $this->hasMany(Ejemplar::class);
    }
}
