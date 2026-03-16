<?php

namespace App\Models;
use App\Models\Compra;

use Illuminate\Database\Eloquent\Model;

class Compra_detalle extends Model
{
    //
    protected $fillable = [
        'compra_id','libro_id','cantidad',
        'precio_unitario','monto_total'
    ];
    public function compra()
    {
        return $this->belongsTo(Compra::class,'compra_id');
    }    
    public function ejemplares()
    {
        return $this->hasMany(Ejemplar::class,'compra_detalle_id');
    }  
    public function libro()
    {
        return $this->belongsTo(libro::class,'libro_id');
    }
}
