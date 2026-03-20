<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Actividad extends Model
{
    //
    protected $table='actividades';
    protected $fillable = ['actividad_categoria_id','fecha_inicio','fecha_fin','titulo','contenido','imagen','referencia','user_id','estado'];

    public function categoria() { return $this->belongsTo(ActividadCategoria::class,'actividad_categoria_id'); }
    public function usuario() { return $this->belongsTo(User::class); }
}
