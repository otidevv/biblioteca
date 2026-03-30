<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\CentroNotificacionesService;

class Actividad extends Model
{
    protected $table = 'actividades';

    protected $fillable = [
        'actividad_categoria_id',
        'fecha_inicio',
        'fecha_fin',
        'hora_inicio',
        'hora_fin',
        'titulo',
        'resumen',
        'contenido',
        'imagen',
        'referencia',
        'lugar',
        'modalidad',
        'destacado',
        'user_id',
        'estado',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'hora_inicio' => 'datetime:H:i',
        'hora_fin' => 'datetime:H:i',
        'destacado' => 'boolean',
    ];

    protected static function booted()
    {
        static::saved(function (Actividad $actividad) {
            app(CentroNotificacionesService::class)->sincronizarActividad($actividad);
        });
    }

    public function categoria()
    {
        return $this->belongsTo(ActividadCategoria::class, 'actividad_categoria_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }
}
