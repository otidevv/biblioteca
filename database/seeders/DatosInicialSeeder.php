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
             *  BIBLIOTECAS
             *  ========================= */
            $bibliotecas = [
                [
                    'codigo' => 'CENTRAL',
                    'nombre' => 'BIBLIOTECA CENTRAL',
                    'direccion' => 'CIUDAD UNIVERSITARIA',
                    'descripcion' => null,
                    'estado' => 'activo',
                ],
                [
                    'codigo' => 'BEIS',
                    'nombre' => 'BIBLIOTECA ESPECIALIZADA DE ING DE SISTEMAS',
                    'direccion' => null,
                    'descripcion' => null,
                    'estado' => 'activo',
                ],
                [
                    'codigo' => 'BEE',
                    'nombre' => 'BIBLIOTECA ESPECIALIZADA DE ENFERMERIA',
                    'direccion' => null,
                    'descripcion' => null,
                    'estado' => 'activo',
                ],
            ];

            foreach ($bibliotecas as $data) {
                Biblioteca::updateOrCreate(
                    ['codigo' => $data['codigo']],
                    $data
                );
            }

            /** =========================
             *  ROL ADMIN
             *  ========================= */
            $rolAdmin = Rol::firstOrCreate(
                ['nombre' => 'ADMIN'],
                ['descripcion' => 'Administrador del sistema']
            );
            Rol::firstOrCreate(
                ['nombre' => 'LECTOR'],
                ['descripcion' => 'Lector del sistema']
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
             *  PERSONA ADMIN
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
             *  ASIGNACIÓN ROL (GLOBAL)
             *  biblioteca_id = NULL
             *  ========================= */
            DB::table('usuario_rol_bibliotecas')->updateOrInsert(
                [
                    'user_id' => $usuarioAdmin->id,
                    'rol_id' => $rolAdmin->id,
                    'biblioteca_id' => null, // 👈 acceso a TODAS
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
                ['nombre' => 'Administración']
            );

            $this->crearPermisos([
                ['administracion.usuarios', 'Gestión de Usuarios'],
                ['administracion.roles_permisos', 'Roles y Permisos'],
                ['administracion.bibliotecas', 'Gestión de Bibliotecas'],
                ['administracion.backups', 'Backups'],
                ['administracion.proveedores', 'Gestión de proveedores'],
                ['administracion.editoriales', 'Gestión de editoriales'],
                ['administracion.tipo_registros', 'Gestión de tipo_registros'],
                ['administracion.autores', 'Gestión de autores'],
            ], $permisoAdmin->id);

            // LECTORES
            $lectores = Permiso::firstOrCreate(
                ['codigo' => 'lectores'],
                ['nombre' => 'Lectores']
            );

            $this->crearPermisos([
                ['lectores.registro', 'Registro de Lectores'],
                ['lectores.historial', 'Historial de Préstamos'],
                ['lectores.penalizaciones', 'Penalizaciones y Multas'],
                ['lectores.importacion', 'Importación de Usuarios'],
            ], $lectores->id);

            // CATÁLOGO
            Permiso::firstOrCreate(['codigo' => 'catalogo'], ['nombre' => 'Catálogo']);

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

            // REPORTES
            Permiso::firstOrCreate(['codigo' => 'reportes'], ['nombre' => 'Reportes']);

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
             *  ASIGNAR TODOS LOS PERMISOS AL ADMIN
             *  ========================= */
            $permisos = Permiso::pluck('id');

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