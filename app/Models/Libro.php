<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Libro extends Model
{
    //
    protected $fillable = [
        'codigo','codigo_dewey','isbn','titulo','paginas',
        'fecha_publicacion','lugar_publicacion','resumen',
        'archivo_indice','imagen','edicion','anio_edicion',
        'idioma','anotaciones','editorial_id','tipo_registro_id','estado'
    ];

    public function autores()
    {
        return $this->belongsToMany(Autor::class);
    }

    public function materias()
    {
        return $this->belongsToMany(Materia::class);
    }

    public function ejemplares()
    {
        return $this->hasMany(Ejemplar::class);
    }
}
