<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservacion extends Model
{
    //
    protected $table = 'reservaciones';
    protected $fillable = ['ejemplar_id','lector_id','duracion','fecha_reservacion','fecha_limite','prestamo','bibliotecario_id','estado'];

    public function ejemplar() { return $this->belongsTo(Ejemplar::class); }
    public function lector() { return $this->belongsTo(User::class,'lector_id'); }
    public function bibliotecario() { return $this->belongsTo(User::class,'bibliotecario_id'); }
}
