<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConstanciaLector extends Model
{
    protected $table = 'constancias_lector';

    protected $fillable = [
        'codigo',
        'user_id',
        'nivel',
        'datos',
        'emitida_at',
        'vence_at',
    ];

    protected $casts = [
        'datos' => 'array',
        'emitida_at' => 'datetime',
        'vence_at' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function estaVigente(): bool
    {
        return $this->vence_at->isFuture();
    }

    // DNI parcial para la verificacion publica: 74****94
    public function dniEnmascarado(): ?string
    {
        $dni = (string) ($this->datos['dni'] ?? '');

        return strlen($dni) >= 4
            ? substr($dni, 0, 2) . str_repeat('*', strlen($dni) - 4) . substr($dni, -2)
            : null;
    }
}
