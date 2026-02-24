<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ejemplar extends Model
{
    //
    protected $table = 'ejemplares';
    protected $fillable = [
        'codigo_interno','libro_id','biblioteca_id','estado'
    ];

    public function libro()
    {
        return $this->belongsTo(Libro::class);
    }
}
