<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Libro extends Model
{
    //
    protected $fillable = [
        'codigo_ant','codigo','codigo_dewey','isbn','titulo','paginas',
        'fecha_publicacion','lugar_publicacion','resumen',
        'archivo_indice','imagen','edicion','anio_edicion',
        'idioma','anotaciones','editorial_id','tipo_registro_id','estado',
        'numero','cod_materia'
    ];

    protected $appends = [
        'imagen_url',
    ];

    public function getImagenUrlAttribute(): string
    {
        $imagen = trim((string) ($this->imagen ?? ''));

        if ($imagen === '') {
            return asset('img/libro-placeholder.png');
        }

        if (Str::startsWith($imagen, ['http://', 'https://'])) {
            return $imagen;
        }

        if (Str::startsWith($imagen, '/storage/')) {
            return asset(ltrim($imagen, '/'));
        }

        if (Str::startsWith($imagen, 'storage/')) {
            return asset($imagen);
        }

        return asset('storage/libros/' . ltrim($imagen, '/'));
    }

    public function autores()
    {
        return $this->belongsToMany(Autor::class, 'autor_libros')
                ->distinct()
                ->withTimestamps();
    }
    public function materias()
    {
        return $this->belongsToMany(Materia::class,'libro_materias')
                ->withTimestamps();
    }

    public function idioma()
    {
        return $this->belongsTo(Idioma::class, 'idioma');
    }

    public function ejemplares()
    {
        return $this->hasMany(Ejemplar::class);
    }
    public function comentarios()
    {
        return $this->hasMany(Comentario::class);
    }
    public function tipo_registro()
    {
        return $this->belongsTo(Tipo_registro::class);
    }
    public function editorial()
    {
        return $this->belongsTo(Editorial::class,'editorial_id');
    }

    public static function siguienteNumeroParaMateria(?string $codMateria): int
    {
        $query = static::query();

        if ($codMateria === null) {
            $query->whereNull('cod_materia');
        } else {
            $query->where('cod_materia', $codMateria);
        }

        $maxNumero = $query->max('numero');

        return $maxNumero ? $maxNumero + 1 : 1;
    }
}
