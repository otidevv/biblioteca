<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Reservacion extends Model
{
    //
    protected $table = 'reservaciones';
    protected $fillable = ['ejemplar_id',
    'lector_id',
    'duracion',
    'fecha_reservacion',
    'fecha_limite',
    'prestamo',
    'bibliotecario_id',
    'estado'];

    protected $casts = [
        'fecha_reservacion' => 'datetime',
        'fecha_limite' => 'date',
    ];

    public function getFechaLimiteRealAttribute()
    {
        return Carbon::parse($this->fecha_limite)->setTime(20, 0, 0);
    }

    public function ejemplar() { 
        return $this->belongsTo(Ejemplar::class); 
    }
    public function lector() { 
        return $this->belongsTo(User::class,'lector_id'); 
    }
    public function bibliotecario() { 
        return $this->belongsTo(User::class,'bibliotecario_id'); 
    }
}
