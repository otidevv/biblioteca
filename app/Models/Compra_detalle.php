<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compra_detalle extends Model
{
    //
    protected $fillable = [
        'compra_id','libro_id','cantidad',
        'precio_unitario','monto_total'
    ];
}
