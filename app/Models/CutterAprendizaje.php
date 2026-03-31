<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CutterAprendizaje extends Model
{
    protected $table = 'cutter_aprendizajes';

    protected $fillable = [
        'clave_autor',
        'codigo_cutter',
        'peso',
    ];
}
