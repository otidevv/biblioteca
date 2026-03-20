<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Actividad_categoria extends Model
{
    //
    protected $fillable = ['abreviatura','nombre','descripcion','user_id','estado'];

    public function usuario() { return $this->belongsTo(User::class); }
    public function actividades() { return $this->hasMany(Actividad::class); }
}
