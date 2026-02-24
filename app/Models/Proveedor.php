<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    //
    protected $table = 'proveedores';
    protected $fillable = [
        'tipo_documento','nro_documento','razon_social',
        'responsable','telefono','correo','direccion','web','estado'
    ];
}
