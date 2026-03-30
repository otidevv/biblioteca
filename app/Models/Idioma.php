<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Idioma extends Model
{
    //
    //protected $table = 'ejemplares';
    protected $fillable = ['nombre'];

    public function libros()
    {
        return $this->hasMany(Libro::class, 'idioma');
    }
}
