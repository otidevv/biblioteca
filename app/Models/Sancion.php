<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sancion extends Model
{
    //
    protected $table = 'sanciones';
    protected $fillable = ['user_id','motivo','fecha_inicio','fecha_fin','estado'];

    public function usuario() { return $this->belongsTo(User::class); }
}
