<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Smalot\PdfParser\Parser;

use App\Models\User;
use App\Models\Rol;
use App\Models\Permiso;
use App\Models\Biblioteca;
use App\Models\Carrera;
use App\Models\Persona;
use App\Models\Codido_cutter;
use App\Models\Dewey;

class DatosInicialSeeder extends Seeder
{
    public function run(): void
    {
        
        /** =========================
         *  NOTACION DEL LOS CODIGOS MALAGA O CUTTER
         *  ========================= */
        $parser = new Parser();
        $pdf = $parser->parseFile(database_path('data/notacion.pdf'));

        $texto = $pdf->getText();

        $lineas = explode("\n", $texto);

        foreach ($lineas as $linea) {

            $linea = trim($linea);

            if($linea == '') continue;

            if(preg_match('/^([A-Za-zÁÉÍÓÚÑñ\.\- ]+)\s+(\d+)\s+([A-Za-zÁÉÍÓÚÑñ\.\- ]+)$/u', $linea, $m)){

                $izq = trim($m[1]);
                $codigo = trim($m[2]);
                $der = trim($m[3]);

                DB::table('codido_cutters')->insert([
                    'codigo'=>$codigo,
                    'nombre'=>$izq,
                    'created_at'=>now(),
                    'updated_at'=>now()
                ]);

                DB::table('codido_cutters')->insert([
                    'codigo'=>$codigo,
                    'nombre'=>$der,
                    'created_at'=>now(),
                    'updated_at'=>now()
                ]);

            }

            elseif(preg_match('/^([A-Za-zÁÉÍÓÚÑñ\.\- ]+)\s+(\d+)$/u', $linea, $m)){

                $nombre = trim($m[1]);
                $codigo = trim($m[2]);

                DB::table('codido_cutters')->insert([
                    'codigo'=>$codigo,
                    'nombre'=>$nombre,
                    'created_at'=>now(),
                    'updated_at'=>now()
                ]);

            }

        }
        /** =========================
         *  tIPO DE REGISTRO
         *  ========================= */
        $data = [
            [
                'id' => 4,
                'codigo' => '001',
                'abreviatura' => 'OBRA',
                'nombre' => 'OBRAS-LITERARIAS',
                'descripcion' => null,
                'estado' => 1,
            ],
            [
                'id' => 5,
                'codigo' => '002',
                'abreviatura' => 'FABULAS',
                'nombre' => 'FABULAS',
                'descripcion' => null,
                'estado' => 1,
            ],
            [
                'id' => 6,
                'codigo' => '003',
                'abreviatura' => 'LIBROS',
                'nombre' => 'LIBROS',
                'descripcion' => null,
                'estado' => 1,
            ],
            [
                'id' => 7,
                'codigo' => 'ECO',
                'abreviatura' => 'ECO',
                'nombre' => 'ECOTURISMO',
                'descripcion' => null,
                'estado' => 1,
            ],
            [
                'id' => 8,
                'codigo' => 'ISI',
                'abreviatura' => 'ISI',
                'nombre' => 'INGENIERIA DE SISTEMAS',
                'descripcion' => null,
                'estado' => 1,
            ],
        ];
        DB::table('tipo_registros')->insert($data);
        /** =========================
         *  IDIOMAS
         *  ========================= */
        $idiomas = [
            ['nombre' => 'INGLÉS'],
            ['nombre' => 'CHINO MANDARÍN'],
            ['nombre' => 'HINDI'],
            ['nombre' => 'ESPAÑOL'],
            ['nombre' => 'FRANCÉS'],
            ['nombre' => 'ÁRABE'],
            ['nombre' => 'BENGALÍ'],
            ['nombre' => 'PORTUGUÉS'],
            ['nombre' => 'RUSO'],
            ['nombre' => 'URDU'],
            ['nombre' => 'INDONESIO'],
            ['nombre' => 'ALEMÁN'],
            ['nombre' => 'JAPONÉS'],
            ['nombre' => 'MARATÍ'],
            ['nombre' => 'TELUGÚ'],
            ['nombre' => 'TURCO'],
            ['nombre' => 'TAMIL'],
            ['nombre' => 'CHINO CANTONÉS (YUE)'],
            ['nombre' => 'VIETNAMITA'],
            ['nombre' => 'TAGALO'],
            ['nombre' => 'CHINO WU'],
            ['nombre' => 'COREANO'],
            ['nombre' => 'PERSA IRANÍ'],
            ['nombre' => 'HAUSA'],
            ['nombre' => 'ÁRABE EGIPCIO'],
            ['nombre' => 'SUAJILI'],
            ['nombre' => 'JAVANÉS'],
            ['nombre' => 'ITALIANO'],
            ['nombre' => 'PANYABÍ OCCIDENTAL'],
            ['nombre' => 'CANARÉS'],
            ['nombre' => 'GUYARATI'],
            ['nombre' => 'TAILANDÉS'],
            ['nombre' => 'AMÁRICO'],
            ['nombre' => 'BHOSHPURI'],
            ['nombre' => 'PANYABÍ'],
            ['nombre' => 'CHINO MǏN NÁN'],
            ['nombre' => 'CHINO JIN'],
            ['nombre' => 'YORUBA'],
            ['nombre' => 'CHINO HAKKA'],
            ['nombre' => 'BIRMANO'],
            ['nombre' => 'ÁRABE SUDANÉS'],
            ['nombre' => 'POLACO'],
            ['nombre' => 'ÁRABE ARGELINO'],
            ['nombre' => 'LINGALA'],
        ];

        DB::table('idiomas')->insert($idiomas);
        /** =========================
         *  CODIGOS DEWEY
         *  ========================= */
        DB::transaction(function(){
            $data = json_decode(
                file_get_contents(database_path('data/dewey.json')),
                true
            );
            foreach ($data as $item) {
                $parent_id = null;
                if (!empty($item['dewy_id'])) {
                    $parent = Dewey::where('codigo', $item['dewy_id'])->first();
                    $parent_id = $parent?->id;
                }
                Dewey::create([
                    'codigo' => $item['codigo'],
                    'nombre' => $item['nombre'],
                    'nivel' => $item['nivel'],
                    'keywords' => $item['keywords'],
                    'dewey_id' => $parent_id
                ]);
            }
        });
        /** =========================
         *  BIBLIOTECAS
         *  ========================= */
        $bibliotecas = [
            [
                'codigo' => 'CENTRAL',
                'nombre' => 'BIBLIOTECA CENTRAL',
                'direccion' => 'CIUDAD UNIVERSITARIA',
                'descripcion' => null,
                'estado' => 1,
            ],
            [
                'codigo' => 'BEIS',
                'nombre' => 'BIBLIOTECA ESPECIALIZADA DE ING DE SISTEMAS',
                'direccion' => null,
                'descripcion' => null,
                'estado' => 1,
            ],
            [
                'codigo' => 'BEE',
                'nombre' => 'BIBLIOTECA ESPECIALIZADA DE ENFERMERIA',
                'direccion' => null,
                'descripcion' => null,
                'estado' => 1,
            ],
            [
                'codigo' => 'BEED',
                'nombre' => 'BIBLIOTECA DE ESCUELA DE EDUCACION',
                'direccion' => null,
                'descripcion' => null,
                'estado' => 1,
            ],
            [
                'codigo' => 'BMHV',
                'nombre' => 'BIBLIOTECA DE ESCUELA DE MEDICINA HUMANA, VETERINARIA',
                'direccion' => null,
                'descripcion' => null,
                'estado' => 1,
            ],
            [
                'codigo' => 'BAGI',
                'nombre' => 'BIBLIOTECA DE ESCUELA DE ING AGRO INDUSTRIAL',
                'direccion' => null,
                'descripcion' => null,
                'estado' => 1,
            ],
            [
                'codigo' => 'BFMA',
                'nombre' => 'BIBLIOTECA DE ESCUELA DE ING FORESTAL MEDIO AMBIENTE',
                'direccion' => null,
                'descripcion' => null,
                'estado' => 1,
            ],
            [
                'codigo' => 'BANI',
                'nombre' => 'BIBLIOTECA DE ESCUELA DE ADMINISTRACION Y NEGOCIOS INTERNACIONALES',
                'direccion' => null,
                'descripcion' => null,
                'estado' => 1,
            ],
            [
                'codigo' => 'BCONT',
                'nombre' => 'BIBLIOTECA DE ESCUELA DE CONTABILIDAD',
                'direccion' => null,
                'descripcion' => null,
                'estado' => 1,
            ],
            [
                'codigo' => 'BDCP',
                'nombre' => 'BIBLIOTECA DE ESCUELA DE DERECHO Y CIENCIAS POLITICAS',
                'direccion' => null,
                'descripcion' => null,
                'estado' => 1,
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
            ['nombre' => 'PROGRAMADOR'],
            ['descripcion' => 'Administrador del sistema']
        );
        Rol::firstOrCreate(
            ['nombre' => 'ADMINISTRADOR'],
            ['descripcion' => 'ADMINISTRADOR del sistema']
        );
        Rol::firstOrCreate(
            ['nombre' => 'ENCARGADO'],
            ['descripcion' => 'ENCARGADO DE REGISTRO']
        );
        Rol::firstOrCreate(
            ['nombre' => 'ATENCION A ESTUDIANTES'],
            ['descripcion' => 'ATENCION A ESTUDIANTES']
        );
        Rol::firstOrCreate(
            ['nombre' => 'LECTOR'],
            ['descripcion' => 'LECTOR']
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
                'estado' => 1,
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
                'biblioteca_id' => null, // acceso a TODAS
            ],
            [
                'estado' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        /** =========================
         *  PERMISOS
         *  ========================= */

        // ADMINISTRACIÓN
        $permisoAdmin = Permiso::updateOrCreate(
            ['codigo' => 'administracion'],
            [
                'nombre' => 'Administración',
                'icono' => '<i class="bi bi-buildings-fill"></i>',
            ]
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
            ['administracion.sanciones', 'Gestión de sanciones'],
            ['administracion.libros', 'Gestión de Libros'],
            ['administracion.libros.traslados', 'Traslados de ejemplares'],
            ['administracion.notificaciones', 'Gestión de notificaciones'],
            ['administracion.actividades', 'Gestión de actividades'],
        ], $permisoAdmin->id);

        // LECTORES
        $lectores = Permiso::updateOrCreate(
            ['codigo' => 'lectores'],
            [
                'nombre' => 'Lectores',
                'icono' => '<i class="bi bi-people-fill"></i>',
            ]
        );

        $this->crearPermisos([
            ['lectores.registro', 'Registro de Lectores'],
            ['lectores.historial', 'Historial de Préstamos'],
            ['lectores.penalizaciones', 'Penalizaciones y Multas'],
            ['lectores.importacion', 'Importación de Usuarios'],
        ], $lectores->id);

        // CATÁLOGO
        Permiso::updateOrCreate(
            ['codigo' => 'catalogo'],
            [
                'nombre' => 'Catálogo',
                'icono' => '<i class="bi bi-journal-bookmark-fill"></i>',
            ]
        );

        // PRÉSTAMOS
        $prestamos = Permiso::updateOrCreate(
            ['codigo' => 'prestamos'],
            [
                'nombre' => 'Préstamos y Gestión',
                'icono' => '<i class="bi bi-arrow-left-right"></i>',
            ]
        );

        $this->crearPermisos([
            ['prestamos.registro', 'Préstamos y Devoluciones'],
            ['prestamos.reservas', 'Reservas'],
            ['prestamos.multas', 'Multas y Sanciones'],
        ], $prestamos->id);

        // REPORTES
        $reportes=Permiso::updateOrCreate(
            ['codigo' => 'reportes'],
            [
                'nombre' => 'Reportes',
                'icono' => '<i class="bi bi-bar-chart-line-fill"></i>',
            ]
        );
        $this->crearPermisos([
            ['reportes.grafico', 'Graficos y estadistico'],
            ['reportes.descargas', 'Descargas'],
        ], $reportes->id);

        // INVENTARIO
        $inventario = Permiso::updateOrCreate(
            ['codigo' => 'inventario'],
            [
                'nombre' => 'Inventario y Extras',
                'icono' => '<i class="bi bi-box-seam-fill"></i>',
            ]
        );

        $this->crearPermisos([
            ['inventario.fisico', 'Inventario Físico'],
            ['inventario.digital', 'Material Digital'],
            ['inventario.notificaciones', 'Notificaciones'],
            ['inventario.compras', 'Gestión de Compras'],
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
