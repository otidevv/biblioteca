<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Prestamo extends Model
{
    //
    protected $fillable = [
        'lector_id',
        'prestamo_lugar',
        'duracion',
        'fecha_prestamo',
        'fecha_limite',
        'fecha_devolucion',
        'observaciones_prestamo',
        'observaciones_devolucion',
        'estado',
        'user_id',
        'ejemplar_id',
        'estado_prestamo',
        'estado_libro'
        ];

    protected $casts = [
        'fecha_prestamo' => 'datetime',
        'fecha_limite' => 'date',
        'fecha_devolucion' => 'datetime',
    ];

    public function getFechaLimiteRealAttribute()
    {
        if ($this->fecha_prestamo && $this->duracion) {
            return Carbon::parse($this->fecha_prestamo)
                ->addDays($this->duracion)
                ->setTime(20, 0, 0);
        }

        if ($this->fecha_limite) {
            return Carbon::parse($this->fecha_limite)->setTime(20, 0, 0);
        }

        return null;
    }

    public function lector() { return $this->belongsTo(User::class,'lector_id'); }
    public function bibliotecario() { return $this->belongsTo(User::class,'user_id'); }
    public function detalles() { return $this->hasMany(DetallePrestamo::class); }
    public function ejemplar() { return $this->belongsTo(Ejemplar::class);}
}
