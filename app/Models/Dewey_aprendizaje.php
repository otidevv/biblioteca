<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dewey_aprendizaje extends Model
{
    //
    protected $table = 'dewey_aprendizajes';

    protected $fillable = [
        'palabra',
        'codigo_dewey',
        'peso'
    ];
}
