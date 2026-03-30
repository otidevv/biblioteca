<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoSancion extends Model
{
    protected $table = 'tipo_sanciones';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'origen_evento',
        'condicion',
        'dias_duracion',
        'monto',
        'requiere_pago',
        'bloquea_prestamos',
        'aplica_automaticamente',
        'estado',
    ];

    public function reglas()
    {
        return $this->hasMany(ReglaSancion::class);
    }
}
