<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Libro extends Model
{
    //
    protected $fillable = [
        'codigo_ant','codigo','codigo_dewey','isbn','titulo','paginas',
        'fecha_publicacion','lugar_publicacion','resumen',
        'archivo_indice','imagen','edicion','anio_edicion',
        'idioma','anotaciones','editorial_id','tipo_registro_id','estado'
    ];

    public function autores()
    {
        return $this->belongsToMany(Autor::class, 'autor_libros')
                ->withTimestamps();
    }
    public function materias()
    {
        return $this->belongsToMany(Materia::class,'libro_materias')
                ->withTimestamps();
    }

    public function ejemplares()
    {
        return $this->hasMany(Ejemplar::class);
    }
    public function tipo_registro()
    {
        return $this->belongsTo(Tipo_registro::class,'tipo_registro_id');
    }
    public function editorial()
    {
        return $this->belongsTo(Editorial::class,'editorial_id');
    }
}
