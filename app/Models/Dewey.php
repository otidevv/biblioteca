<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dewey extends Model
{
    //
    protected $table = 'deweys';
    protected $fillable = ['codigo', 'nombre', 'keywords', 'nivel'];
}
