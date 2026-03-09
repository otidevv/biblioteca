<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Materia extends Model
{
    //
    protected $table = 'materias';
    protected $fillable = [
        'codigo','abreviatura','nombre','descripcion','estado'
    ];

    public function libros()
    {
        return $this->belongsToMany(Libro::class, 'libro_materias');
    }
}
