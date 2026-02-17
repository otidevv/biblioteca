<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\Rol;
use App\Models\Permiso;
use App\Models\Biblioteca;
use App\Models\Carrera;
use App\Models\Persona;

class DatosInicialSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            /** =========================
             *  BIBLIOTECA
             *  ========================= */
            $biblioteca = Biblioteca::firstOrCreate(
                ['codigo' => 'BIB-001'],
                [
                    'nombre' => 'Biblioteca Central',
                    'direccion' => 'Principal',
                    'estado' => 'activo',
                ]
            );

            /** =========================
             *  ROL ADMIN
             *  ========================= */
            $rolAdmin = Rol::firstOrCreate(
                ['nombre' => 'ADMIN'],
                ['descripcion' => 'Administrador del sistema']
            );

            /** =========================
             *  CARRERA
             *  ========================= */
            $carrera = Carrera::firstOrCreate(
                ['codigo' => 'ADM'],
                [
                    'nombre' => 'Administración del Sistema',
                    'facultad' => 'Sistemas',
                    'descripcion' => 'Carrera administrativa del sistema',
                    'activo' => true,
                ]
            );

            /** =========================
             *  PERSONA (ADMIN)
             *  ========================= */
            $personaAdmin = Persona::firstOrCreate(
                ['dni' => '00000000'],
                [
                    'codigo_institucional' => 'ADMIN001',
                    'nombres' => 'Administrador',
                    'apellido_paterno' => 'Sistema',
                    'apellido_materno' => 'Sistema',
                    'sexo' => 'O',
                    'email_personal' => 'admin@biblioteca.local',
                    'tipo_persona' => 'ADMINISTRATIVO',
                    'carrera_id' => $carrera->id,
                    'activo' => true,
                ]
            );

            /** =========================
             *  USUARIO ADMIN
             *  ========================= */
            $usuarioAdmin = User::firstOrCreate(
                ['email' => 'admin@biblioteca.local'],
                [
                    'uuid' => Str::uuid(),
                    'name' => 'admin',
                    'password' => Hash::make('12345678'),
                    'tipo_usuario' => 'ADMIN',
                    'estado' => 'activo',
                    'origen' => 'local',
                    'persona_id' => $personaAdmin->id,
                ]
            );

            /** =========================
             *  ASIGNACIÓN ROL + BIBLIOTECA
             *  ========================= */
            DB::table('usuario_rol_bibliotecas')->updateOrInsert(
                [
                    'user_id' => $usuarioAdmin->id,
                    'rol_id' => $rolAdmin->id,
                    'biblioteca_id' => $biblioteca->id,
                ],
                [
                    'activo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            /** =========================
             *  PERMISOS
             *  ========================= */

            // ADMINISTRACIÓN
            $permisoAdmin = Permiso::firstOrCreate(
                ['codigo' => 'administracion'],
                [
                    'nombre' => 'Administración',
                    'icono' => '<svg class="w-6 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"> <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path> <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path> </svg>',
                ]
            );

            $this->crearPermisos([
                ['administracion.usuarios', 'Gestión de Usuarios'],
                ['administracion.roles_permisos', 'Roles y Permisos'],
                ['administracion.bibliotecas', 'Bibliotecas'],
                ['administracion.backups', 'Backups'],
            ], $permisoAdmin->id);

            // LECTORES
            $lectores = Permiso::firstOrCreate(
                ['codigo' => 'lectores'],
                [
                    'nombre' => 'Lectores',
                    'icono' => '<svg class="w-6 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"> <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path> </svg>',
                ]
            );

            $this->crearPermisos([
                ['lectores.registro', 'Registro de Lectores'],
                ['lectores.historial', 'Historial de Préstamos'],
                ['lectores.penalizaciones', 'Penalizaciones y Multas'],
                ['lectores.importacion', 'Importación de Usuarios'],
            ], $lectores->id);

            // CATÁLOGO
            Permiso::firstOrCreate(
                ['codigo' => 'catalogo'],
                ['nombre' => 'Catálogo']
            );

            // PRÉSTAMOS
            $prestamos = Permiso::firstOrCreate(
                ['codigo' => 'prestamos'],
                ['nombre' => 'Préstamos y Gestión']
            );

            $this->crearPermisos([
                ['prestamos.registro', 'Préstamos y Devoluciones'],
                ['prestamos.reservas', 'Reservas'],
                ['prestamos.multas', 'Multas y Sanciones'],
            ], $prestamos->id);

            // BÚSQUEDA
            Permiso::firstOrCreate(
                ['codigo' => 'busqueda'],
                ['nombre' => 'Búsqueda y Consulta']
            );

            // REPORTES
            Permiso::firstOrCreate(
                ['codigo' => 'reportes'],
                ['nombre' => 'Reportes']
            );

            // INVENTARIO
            $inventario = Permiso::firstOrCreate(
                ['codigo' => 'inventario'],
                ['nombre' => 'Inventario y Extras']
            );

            $this->crearPermisos([
                ['inventario.fisico', 'Inventario Físico'],
                ['inventario.digital', 'Material Digital'],
                ['notificaciones', 'Notificaciones'],
            ], $inventario->id);
            /** =========================
             *  ASIGNAR PERMISOS AL ROL ADMIN
             *  ========================= */

            // Obtener TODOS los permisos
            $permisos = Permiso::pluck('id');

            // Asignar todos los permisos al rol ADMIN
            foreach ($permisos as $permisoId) {
                DB::table('rol_permisos')->updateOrInsert(
                    [
                        'rol_id' => $rolAdmin->id,
                        'permiso_id' => $permisoId,
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        });
    }

    private function crearPermisos(array $permisos, int $permisoPadreId): void
    {
        foreach ($permisos as [$codigo, $nombre]) {
            Permiso::firstOrCreate(
                ['codigo' => $codigo],
                [
                    'nombre' => $nombre,
                    'descripcion' => $nombre,
                    'permiso_id' => $permisoPadreId,
                ]
            );
        }
    }
}
