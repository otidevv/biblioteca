<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use App\Models\Rol;
use App\Models\User;
use App\Models\Permiso;
use \App\Models\Usuario_rol_biblioteca;
use App\Models\NotificacionDestinatario;
use App\Models\SeguimientoLibro;
use App\Models\HistorialBusquedaLibro;
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'name',
        'email',
        'password',
        'tipo_usuario',
        'estado',
        'origen',
        'persona_id',
       ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

            // Identidad global
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }
    protected static function booted()
    {
        static::creating(function ($user) {
            if (empty($user->uuid)) {
                $user->uuid = (string) Str::uuid();
            }
        });
    }
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Rol::class,
            'usuario_rol_bibliotecas',
            'user_id',
            'rol_id'
        )->withPivot('biblioteca_id', 'estado');
    }
    public function usuarioRolBibliotecas()
    {
        return $this->hasMany(Usuario_rol_biblioteca::class, 'user_id');
    }

    public function notificacionDestinatarios()
    {
        return $this->hasMany(NotificacionDestinatario::class, 'user_id');
    }

    public function seguimientosLibros()
    {
        return $this->hasMany(SeguimientoLibro::class, 'user_id');
    }

    public function historialBusquedasLibros()
    {
        return $this->hasMany(HistorialBusquedaLibro::class, 'user_id');
    }

    public function movimientosEjemplaresSolicitados()
    {
        return $this->hasMany(MovimientoEjemplar::class, 'solicitado_por_user_id');
    }

    public function movimientosEjemplaresResueltos()
    {
        return $this->hasMany(MovimientoEjemplar::class, 'resuelto_por_user_id');
    }
}
