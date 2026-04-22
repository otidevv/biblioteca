<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sancion extends Model
{
    //
    protected $table = 'sanciones';
    protected $fillable = [
        'user_id',
        'prestamo_id',
        'reservacion_id',
        'tipo_sancion_id',
        'tipo',
        'codigo_pago',
        'motivo',
        'fecha_inicio',
        'fecha_fin',
        'duracion',
        'observaciones',
        'detalles_termino',
        'bibliotecario_id',
        'estado',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    public function usuario() { return $this->belongsTo(User::class); }
    public function bibliotecario() { return $this->belongsTo(User::class, 'bibliotecario_id'); }
    public function prestamo() { return $this->belongsTo(Prestamo::class); }
    public function reservacion() { return $this->belongsTo(Reservacion::class); }
    public function tipoSancion() { return $this->belongsTo(TipoSancion::class); }
}
