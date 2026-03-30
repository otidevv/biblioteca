<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Autor extends Model
{
    //
    protected $fillable = ['nombres','apellidos','pais','estado'];
    protected $table = 'autores';
    public function libros()
    {
        return $this->belongsToMany(Libro::class, 'autor_libros')
                ->distinct()
                ->withTimestamps();
    }
    public function pais()
    {
        return $this->belongsTo(Pais::class, 'pais');
    }
}
