<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificacionDestinatario extends Model
{
    protected $table = 'notificacion_destinatarios';

    protected $fillable = [
        'notificacion_id',
        'user_id',
        'leido',
        'fecha_lectura',
        'archivado',
        'enviado_email',
        'fecha_envio_email',
    ];

    protected $casts = [
        'leido' => 'boolean',
        'archivado' => 'boolean',
        'enviado_email' => 'boolean',
        'fecha_lectura' => 'datetime',
        'fecha_envio_email' => 'datetime',
    ];

    public function notificacion()
    {
        return $this->belongsTo(Notificacion::class, 'notificacion_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
