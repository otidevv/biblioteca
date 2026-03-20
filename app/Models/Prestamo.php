<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prestamo extends Model
{
    //
    protected $fillable = ['lector_id','prestamo_lugar','duracion','fecha_prestamo','fecha_limite','fecha_devolucion','observaciones','estado','user_id'];

    public function lector() { return $this->belongsTo(User::class,'lector_id'); }
    public function bibliotecario() { return $this->belongsTo(User::class,'user_id'); }
    public function detalles() { return $this->hasMany(DetallePrestamo::class); }
}
