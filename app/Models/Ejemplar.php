<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ejemplar extends Model
{
    //
    protected $table = 'ejemplares';
    protected $fillable = [
       'siaf','tipo','codigo_dewey','codigo_interno','libro_id','biblioteca_id','estado','Compra_detalle'
    ];

    public function libro()
    {
        return $this->belongsTo(Libro::class, 'libro_id');
    }
    public function detalle_compra()
    {
        return $this->belongsTo(Compra_detalle::class,'Compra_detalle');
    }
    public function biblioteca()
    {
        return $this->belongsTo(Biblioteca::class, 'biblioteca_id');
    }
}
