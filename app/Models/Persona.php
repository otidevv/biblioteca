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
        'tipo_persona',
        'carrera_id',
        'estado_academico',
        'activo'
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
    ];

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


