<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visita extends Model
{
    public $timestamps = false;

    protected $fillable = ['session_id', 'ip', 'user_id', 'fecha', 'created_at'];

    protected $casts = ['fecha' => 'date'];
}
