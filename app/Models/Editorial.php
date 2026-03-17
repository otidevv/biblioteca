<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Editorial extends Model
{
    //
    protected $table = 'editoriales';
    protected $fillable = [
        'tipo_documento',
        'nro_documento',
        'nombre',
        'responsable',
        'telefono',
        'correo',
        'direccion',
        'web',
        'pais',
        'estado'
    ];

    public function libros()
    {
        return $this->hasMany(Libro::class);
    }
}
