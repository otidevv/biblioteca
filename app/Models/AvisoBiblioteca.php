<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AvisoBiblioteca extends Model
{
    protected $table = 'aviso_bibliotecas';

    protected $fillable = [
        'titulo',
        'contenido',
        'tipo',
        'accion_url',
        'accion_texto',
        'inicio_publicacion',
        'fin_publicacion',
        'es_destacado',
        'estado',
        'user_id',
    ];

    protected $casts = [
        'inicio_publicacion' => 'datetime',
        'fin_publicacion' => 'datetime',
        'es_destacado' => 'boolean',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }
}
