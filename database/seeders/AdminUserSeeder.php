<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\Persona;
use App\Models\Rol;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $persona = Persona::firstOrCreate(
            ['dni' => '99999999'],
            [
                'codigo_institucional' => 'DEV001',
                'nombres' => 'Michael',
                'apellido_paterno' => 'Dmamanic',
                'apellido_materno' => 'Dev',
                'sexo' => 'M',
                'email_personal' => 'dmamanic@unamad.edu.pe',
                'tipo_persona' => 'ADMINISTRATIVO',
                'activo' => true,
            ]
        );

        $usuario = User::firstOrCreate(
            ['email' => 'dmamanic@unamad.edu.pe'],
            [
                'uuid' => Str::uuid(),
                'name' => 'dmamanic',
                'password' => Hash::make('12345678'),
                'tipo_usuario' => 'ADMIN',
                'estado' => 1,
                'origen' => 'local',
                'persona_id' => $persona->id,
            ]
        );

        $rol = Rol::where('nombre', 'PROGRAMADOR')->first();

        if ($rol) {
            DB::table('usuario_rol_bibliotecas')->updateOrInsert(
                [
                    'user_id' => $usuario->id,
                    'rol_id' => $rol->id,
                    'biblioteca_id' => null,
                ],
                [
                    'estado' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $this->command->info('Usuario admin creado: dmamanic@unamad.edu.pe / 12345678');
    }
}
