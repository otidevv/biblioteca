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
        'biblioteca_id',
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

    public function usuario() { return $this->belongsTo(User::class, 'user_id'); }
    public function bibliotecario() { return $this->belongsTo(User::class, 'bibliotecario_id'); }
    public function prestamo() { return $this->belongsTo(Prestamo::class); }
    public function reservacion() { return $this->belongsTo(Reservacion::class); }
    public function tipoSancion() { return $this->belongsTo(TipoSancion::class); }
    public function biblioteca() { return $this->belongsTo(Biblioteca::class); }

    // Sancion activa que restringe al lector en la biblioteca indicada.
    // Las sanciones sin biblioteca (registros antiguos) aplican en todas.
    public function scopeVigenteEnBiblioteca($query, ?int $bibliotecaId)
    {
        return $query->where('estado', 1)
            ->whereDate('fecha_fin', '>=', now()->toDateString())
            ->where(function ($sub) use ($bibliotecaId) {
                $sub->whereNull('biblioteca_id')
                    ->when($bibliotecaId, fn ($q) => $q->orWhere('biblioteca_id', $bibliotecaId));
            });
    }
}
