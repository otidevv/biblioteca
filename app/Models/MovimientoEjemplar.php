<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovimientoEjemplar extends Model
{
    public const ESTADO_PENDIENTE = 'pendiente';
    public const ESTADO_ACEPTADO = 'aceptado';
    public const ESTADO_RECHAZADO = 'rechazado';
    public const ESTADO_CANCELADO = 'cancelado';

    protected $table = 'movimiento_ejemplares';

    protected $fillable = [
        'ejemplar_id',
        'libro_id',
        'biblioteca_origen_id',
        'biblioteca_destino_id',
        'solicitado_por_user_id',
        'resuelto_por_user_id',
        'estado',
        'solicitado_en',
        'resuelto_en',
    ];

    protected $casts = [
        'solicitado_en' => 'datetime',
        'resuelto_en' => 'datetime',
    ];

    public function ejemplar()
    {
        return $this->belongsTo(Ejemplar::class, 'ejemplar_id');
    }

    public function libro()
    {
        return $this->belongsTo(Libro::class, 'libro_id');
    }

    public function bibliotecaOrigen()
    {
        return $this->belongsTo(Biblioteca::class, 'biblioteca_origen_id');
    }

    public function bibliotecaDestino()
    {
        return $this->belongsTo(Biblioteca::class, 'biblioteca_destino_id');
    }

    public function solicitadoPor()
    {
        return $this->belongsTo(User::class, 'solicitado_por_user_id');
    }

    public function resueltoPor()
    {
        return $this->belongsTo(User::class, 'resuelto_por_user_id');
    }
}
