<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permiso extends Model
{
    //
    
    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'icono',
        'permiso_id',
    ];    
    public function hijos()
    {
        return $this->hasMany(Permiso::class, 'permiso_id');
    }

    public function padre()
    {
        return $this->belongsTo(Permiso::class, 'permiso_id');
    }
}
