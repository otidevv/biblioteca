<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialBusquedaLibro extends Model
{
    protected $table = 'historial_busquedas_libros';

    protected $fillable = [
        'user_id',
        'termino',
        'libro_id',
        'fecha_busqueda',
    ];

    protected $casts = [
        'fecha_busqueda' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function libro()
    {
        return $this->belongsTo(Libro::class);
    }
}
