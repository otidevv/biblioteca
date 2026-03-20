<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Detalle_prestamo extends Model
{
    //
    protected $table='detalle_prestamos';
    protected $fillable = ['prestamo_id','ejemplar_id','user_id','observaciones','estado'];

    public function prestamo() { return $this->belongsTo(Prestamo::class); }
    public function registro() { return $this->belongsTo(Registro::class); }
    public function ejemplar() { return $this->belongsTo(Ejemplar::class); }
    public function usuario() { return $this->belongsTo(User::class); }
}
