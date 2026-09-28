<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Prestamo extends Model
{
    const EN_SALA = 0;
    const A_CASA = 1;

    // Hora de cierre: los prestamos vencen a esta hora del dia limite.
    // Los prestamos en sala (duracion 0) vencen hoy a esta hora.
    const HORA_CIERRE = 20;

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
        if ($this->fecha_prestamo && $this->duracion !== null) {
            return Carbon::parse($this->fecha_prestamo)
                ->addDays($this->duracion)
                ->setTime(self::HORA_CIERRE, 0, 0);
        }

        if ($this->fecha_limite) {
            return Carbon::parse($this->fecha_limite)->setTime(self::HORA_CIERRE, 0, 0);
        }

        return null;
    }

    public static function cierreDeHoy(): Carbon
    {
        return today()->setTime(self::HORA_CIERRE, 0, 0);
    }

    public function lector() { return $this->belongsTo(User::class,'lector_id'); }
    public function bibliotecario() { return $this->belongsTo(User::class,'user_id'); }
    public function detalles() { return $this->hasMany(DetallePrestamo::class); }
    public function ejemplar() { return $this->belongsTo(Ejemplar::class);}
}
