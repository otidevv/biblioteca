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
             *  MATERIA
             *  ========================= */
            DB::table('materias')->insert([
                        [
                            'codigo' => '001',
                            'abreviatura' => 'CI',
                            'nombre' => 'COMPUTACION E INFORMATICA',
                            'descripcion' => null,
                            'estado' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                        [
                            'codigo' => '0652955D',
                            'abreviatura' => 'CEI',
                            'nombre' => 'COMPUTACION E INFORMATICA',
                            'descripcion' => 'Materia de COMPUTACION E INFORMATICA',
                            'estado' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                        [
                            'codigo' => 'D35DCE48',
                            'abreviatura' => 'INV',
                            'nombre' => 'INVESTIGACION',
                            'descripcion' => 'Materia de INVESTIGACION',
                            'estado' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                        [
                            'codigo' => '500',
                            'abreviatura' => 'MAT',
                            'nombre' => 'MATEMATICA',
                            'descripcion' => 'Materia de MATEMATICA',
                            'estado' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                        [
                            'codigo' => 'A2336E80',
                            'abreviatura' => 'QUI',
                            'nombre' => 'QUIMICA',
                            'descripcion' => 'Materia de QUIMICA',
                            'estado' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                        [
                            'codigo' => 'B4176A09',
                            'abreviatura' => 'FIS',
                            'nombre' => 'FISICA',
                            'descripcion' => 'Materia de FISICA',
                            'estado' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                        [
                            'codigo' => 'FB23EF1B',
                            'abreviatura' => 'OL',
                            'nombre' => 'OBRAS LITERARIAS',
                            'descripcion' => 'Materia de OBRAS LITERARIAS',
                            'estado' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                        [
                            'codigo' => '30CFB849',
                            'abreviatura' => 'HIS',
                            'nombre' => 'HISTORIA',
                            'descripcion' => 'Materia de HISTORIA',
                            'estado' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                        [
                            'codigo' => 'F062EE99',
                            'abreviatura' => 'GEO',
                            'nombre' => 'GEOGRAFIA',
                            'descripcion' => 'Materia de GEOGRAFIA',
                            'estado' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                        [
                            'codigo' => 'PSI',
                            'abreviatura' => 'PSI',
                            'nombre' => 'PSICOLOGIA',
                            'descripcion' => null,
                            'estado' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                    ]);
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
            $carrera = Carrera::firstOrCreate(
                ['codigo' => 'ADM'],
                [
                    'nombre' => 'ADMINISTRACION',
                    'facultad' => 'FACULTAD DE CIENCIAS EMPRESARIALES',
                    'descripcion' => 'CARRERA PROFESIONAL DE ADMINISTRACION',
                    'activo' => true,
                ]
            );

            $carrera = Carrera::firstOrCreate(
                ['codigo' => 'CON'],
                [
                    'nombre' => 'CONTABILIDAD Y FINANZAS',
                    'facultad' => 'FACULTAD DE CIENCIAS EMPRESARIALES',
                    'descripcion' => 'CARRERA PROFESIONAL DE CONTABILIDAD Y FINANZAS',
                    'activo' => true,
                ]
            );

            $carrera = Carrera::firstOrCreate(
                ['codigo' => 'ECO'],
                [
                    'nombre' => 'ECOTURISMO',
                    'facultad' => 'FACULTAD DE ECOTURISMO',
                    'descripcion' => 'CARRERA PROFESIONAL DE ECOTURISMO',
                    'activo' => true,
                ]
            );

            $carrera = Carrera::firstOrCreate(
                ['codigo' => 'FOR'],
                [
                    'nombre' => 'INGENIERIA FORESTAL Y MEDIO AMBIENTE',
                    'facultad' => 'FACULTAD DE INGENIERIA FORESTAL Y MEDIO AMBIENTE',
                    'descripcion' => 'CARRERA PROFESIONAL DE INGENIERIA FORESTAL Y MEDIO AMBIENTE',
                    'activo' => true,
                ]
            );

            $carrera = Carrera::firstOrCreate(
                ['codigo' => 'SIS'],
                [
                    'nombre' => 'INGENIERIA DE SISTEMAS E INFORMATICA',
                    'facultad' => 'FACULTAD DE INGENIERIA',
                    'descripcion' => 'CARRERA PROFESIONAL DE INGENIERIA DE SISTEMAS E INFORMATICA',
                    'activo' => true,
                ]
            );

            $carrera = Carrera::firstOrCreate(
                ['codigo' => 'AGRO'],
                [
                    'nombre' => 'INGENIERIA AGROINDUSTRIAL',
                    'facultad' => 'FACULTAD DE INGENIERIA',
                    'descripcion' => 'CARRERA PROFESIONAL DE INGENIERIA AGROINDUSTRIAL',
                    'activo' => true,
                ]
            );

            $carrera = Carrera::firstOrCreate(
                ['codigo' => 'MEDVET'],
                [
                    'nombre' => 'MEDICINA VETERINARIA Y ZOOTECNIA',
                    'facultad' => 'FACULTAD DE MEDICINA VETERINARIA Y ZOOTECNIA',
                    'descripcion' => 'CARRERA PROFESIONAL DE MEDICINA VETERINARIA Y ZOOTECNIA',
                    'activo' => true,
                ]
            );

            $carrera = Carrera::firstOrCreate(
                ['codigo' => 'DER'],
                [
                    'nombre' => 'DERECHO Y CIENCIAS POLITICAS',
                    'facultad' => 'FACULTAD DE DERECHO Y CIENCIAS POLITICAS',
                    'descripcion' => 'CARRERA PROFESIONAL DE DERECHO Y CIENCIAS POLITICAS',
                    'activo' => true,
                ]
            );

            $carrera = Carrera::firstOrCreate(
                ['codigo' => 'ENF'],
                [
                    'nombre' => 'ENFERMERIA',
                    'facultad' => 'FACULTAD DE CIENCIAS DE LA SALUD',
                    'descripcion' => 'CARRERA PROFESIONAL DE ENFERMERIA',
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
                ['inventario.notificaciones', 'Notificaciones'],
                ['inventario.compras', 'Gestión de Compras'],
                ['inventario.libros', 'Gestión de Libros'],
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