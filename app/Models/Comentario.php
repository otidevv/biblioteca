<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comentario extends Model
{
    //
    protected $fillable = ['libro_id','user_id','comentario','calificacion'];

    public function libro() { return $this->belongsTo(Libro::class); }
    public function usuario() { return $this->belongsTo(User::class,'user_id'); }
}
