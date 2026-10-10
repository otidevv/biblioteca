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
        'es_libre',
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
        'es_libre' => 'boolean',
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

    // Misma regla que fecha_limite_real, en SQL, para filtrar y contar vencidos en la base.
    public static function sqlFechaLimiteReal(): string
    {
        $cierre = sprintf("'%02d:00:00'", self::HORA_CIERRE);

        return "COALESCE("
            . "CASE WHEN prestamos.fecha_prestamo IS NOT NULL AND prestamos.duracion IS NOT NULL "
            . "THEN TIMESTAMP(DATE_ADD(DATE(prestamos.fecha_prestamo), INTERVAL prestamos.duracion DAY), {$cierre}) END, "
            . "TIMESTAMP(prestamos.fecha_limite, {$cierre}))";
    }

    public function scopeEnCurso($query)
    {
        return $query->where('prestamos.estado', 1);
    }

    public function scopeVencidos($query)
    {
        return $query->enCurso()->whereRaw(self::sqlFechaLimiteReal() . ' < ?', [now()]);
    }

    public function scopeDevueltos($query)
    {
        return $query->whereIn('prestamos.estado', [2, 3]);
    }

    public function lector() { return $this->belongsTo(User::class,'lector_id'); }
    public function bibliotecario() { return $this->belongsTo(User::class,'user_id'); }
    public function detalles() { return $this->hasMany(DetallePrestamo::class); }
    public function ejemplar() { return $this->belongsTo(Ejemplar::class);}
}
