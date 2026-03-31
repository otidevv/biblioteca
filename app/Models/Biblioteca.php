<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Biblioteca extends Model
{
    protected $fillable = [
        'codigo',
        'nombre',
        'direccion',
        'descripcion',
        'estado',
        'imagen'
    ];

    protected $appends = [
        'imagen_url',
    ];

    public function getImagenUrlAttribute(): string
    {
        $imagen = trim((string) ($this->imagen ?? ''));

        if ($imagen === '') {
            return asset('img/biblioteca-placeholder.svg');
        }

        if (Str::startsWith($imagen, ['http://', 'https://'])) {
            return $imagen;
        }

        if (Str::startsWith($imagen, '/storage/')) {
            return asset(ltrim($imagen, '/'));
        }

        if (Str::startsWith($imagen, 'storage/')) {
            return asset($imagen);
        }

        return asset('storage/bibliotecas/' . ltrim($imagen, '/'));
    }

    public function ejemplares(){
        return $this->hasMany(Ejemplar::class);
    }
}
