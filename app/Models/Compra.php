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

    public function detalles()
    {
        return $this->hasMany(CompraDetalle::class);
    }
}
