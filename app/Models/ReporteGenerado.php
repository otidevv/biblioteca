<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReporteGenerado extends Model
{
    protected $table = 'reportes_generados';

    protected $fillable = [
        'user_id',
        'modulo',
        'formato',
        'filtros',
        'estado',
        'archivo_nombre',
        'archivo_ruta',
        'total_registros',
        'error',
        'solicitado_en',
        'procesado_en',
    ];

    protected $casts = [
        'filtros' => 'array',
        'solicitado_en' => 'datetime',
        'procesado_en' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
