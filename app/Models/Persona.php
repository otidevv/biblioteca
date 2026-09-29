<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Persona extends Model
{
    protected $fillable = [
        'dni',
        'codigo_institucional',
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'fecha_nacimiento',
        'sexo',
        'direccion',
        'telefono',
        'email_personal',
        'foto',
        'tipo_persona',
        'carrera_id',
        'estado_academico',
        'tipo_carnet',
        'fecha_carnet_duplicado',
        'estado'
    ];

    const CARNET_ORIGINAL = 'ORIGINAL';
    const CARNET_DUPLICADO = 'DUPLICADO';

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'fecha_carnet_duplicado' => 'date:Y-m-d',
    ];

    public function tieneCarnetDuplicado(): bool
    {
        return $this->tipo_carnet === self::CARNET_DUPLICADO;
    }

    public function carrera()
    {
        return $this->belongsTo(Carrera::class);
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }

    // Helpers útiles
    public function getNombreCompletoAttribute()
    {
        return "{$this->apellido_paterno} {$this->apellido_materno}, {$this->nombres}";
    }
}

