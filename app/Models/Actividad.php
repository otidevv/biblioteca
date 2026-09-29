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

    // Actividades publicadas que aun no terminan (en curso o proximas).
    public function scopeVigentes($query)
    {
        return $query->where('estado', 1)
            ->whereRaw('COALESCE(fecha_fin, fecha_inicio) >= ?', [today()->toDateString()]);
    }

    public function scopeFinalizadas($query)
    {
        return $query->where('estado', 1)
            ->whereRaw('COALESCE(fecha_fin, fecha_inicio) < ?', [today()->toDateString()]);
    }

    /** proxima | en_curso | finalizada */
    public function estadoAgenda(): string
    {
        $inicio = $this->fecha_inicio?->copy()->startOfDay();
        $fin = ($this->fecha_fin ?? $this->fecha_inicio)?->copy()->endOfDay();

        if (! $inicio || ! $fin) {
            return 'proxima';
        }

        if (now()->lt($inicio)) {
            return 'proxima';
        }

        return now()->lte($fin) ? 'en_curso' : 'finalizada';
    }

    public function getImagenUrlAttribute(): ?string
    {
        if (! $this->imagen) {
            return null;
        }

        $ruta = ltrim((string) $this->imagen, '/');

        if (preg_match('#^https?://#i', $ruta)) {
            return $ruta;
        }

        return asset(str_starts_with($ruta, 'storage/') ? $ruta : 'storage/' . $ruta);
    }

    // La referencia puede ser un ponente o un enlace: solo se enlaza si es http(s).
    public function getReferenciaUrlAttribute(): ?string
    {
        $referencia = trim((string) $this->referencia);

        return preg_match('#^https?://[^\s]+$#i', $referencia) ? $referencia : null;
    }

    const MESES_CORTOS = ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];

    // "Martes 30 de septiembre", "30 – 31 de octubre" o "30 de septiembre – 2 de octubre" (+ año si no es el actual).
    public function rangoFechas(): string
    {
        $inicio = $this->fecha_inicio?->copy()->locale('es');
        $fin = ($this->fecha_fin ?? $this->fecha_inicio)?->copy()->locale('es');

        if (! $inicio) {
            return 'Fecha por confirmar';
        }

        $anio = $fin->year !== now()->year ? ' de ' . $fin->year : '';

        if ($inicio->isSameDay($fin)) {
            return ucfirst($inicio->translatedFormat('l j \d\e F')) . $anio;
        }

        if ($inicio->isSameMonth($fin)) {
            return $inicio->format('j') . ' – ' . $fin->translatedFormat('j \d\e F') . $anio;
        }

        return $inicio->translatedFormat('j \d\e F') . ' – ' . $fin->translatedFormat('j \d\e F') . $anio;
    }

    public function mesCorto(): string
    {
        return $this->fecha_inicio ? self::MESES_CORTOS[$this->fecha_inicio->month - 1] : '—';
    }

    public function horario(): ?string
    {
        if (! $this->hora_inicio) {
            return null;
        }

        return $this->hora_inicio->format('H:i') . ($this->hora_fin ? ' – ' . $this->hora_fin->format('H:i') : '');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }
}
