<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    //
    protected $fillable = [
        'numero_siaf','fecha_compra','proveedor_id',
        'usuario_id','monto_total','observaciones'
    ];

    public function compra_detalles()
    {
        return $this->hasMany(Compra_detalle::class,'compra_id');
    }
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class,'proveedor_id');
    }
}
