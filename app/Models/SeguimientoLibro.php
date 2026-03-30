<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeguimientoLibro extends Model
{
    protected $table = 'seguimiento_libros';

    protected $fillable = [
        'user_id',
        'libro_id',
        'motivo',
        'estado',
        'notificado_disponibilidad',
        'fecha_notificacion',
        'ultima_busqueda',
    ];

    protected $casts = [
        'notificado_disponibilidad' => 'boolean',
        'fecha_notificacion' => 'datetime',
        'ultima_busqueda' => 'datetime',
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
