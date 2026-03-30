<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReglaSancion extends Model
{
    protected $table = 'reglas_sanciones';

    protected $fillable = [
        'tipo_sancion_id',
        'evento',
        'dias_desde',
        'dias_hasta',
        'cantidad_minima',
        'cantidad_maxima',
        'duracion_dias',
        'monto',
        'requiere_aprobacion',
        'estado',
    ];

    public function tipoSancion()
    {
        return $this->belongsTo(TipoSancion::class);
    }
}
