<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    protected $table = 'notificaciones';

    protected $fillable = [
        'titulo',
        'contenido',
        'tipo',
        'canal',
        'audiencia',
        'user_id_origen',
        'accion_url',
        'entidad_tipo',
        'entidad_id',
        'es_programada',
        'fecha_publicacion',
        'fecha_expiracion',
        'estado',
    ];

    protected $casts = [
        'es_programada' => 'boolean',
        'fecha_publicacion' => 'datetime',
        'fecha_expiracion' => 'datetime',
    ];

    public function origen()
    {
        return $this->belongsTo(User::class, 'user_id_origen');
    }

    public function destinatarios()
    {
        return $this->hasMany(NotificacionDestinatario::class, 'notificacion_id');
    }
}
