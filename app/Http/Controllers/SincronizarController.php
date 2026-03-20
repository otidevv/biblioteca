<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SincronizarController extends Controller
{
    public function sincronizar()
    {
        DB::beginTransaction();

        try {

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            $this->ejecutarPaso('paises', fn() => $this->paises());
            $this->ejecutarPaso('editoriales', fn() => $this->editoriales());
            $this->ejecutarPaso('autores', fn() => $this->autores());
            $this->ejecutarPaso('materias', fn() => $this->materias());
            $this->ejecutarPaso('carreras', fn() => $this->carreras());
            $this->ejecutarPaso('personas', fn() => $this->personas());
            $this->ejecutarPaso('usuarios', fn() => $this->usuarios());
            $this->ejecutarPaso('libros', fn() => $this->libros());
            $this->ejecutarPaso('relaciones', fn() => $this->libro_relaciones());
            $this->ejecutarPaso('compras', fn() => $this->compras());
            $this->ejecutarPaso('ejemplares', fn() => $this->ejemplares());

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            DB::commit();

            return response()->json([
                'success' => true,
                'mensaje' => '✅ Sincronización completada correctamente'
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Error en sincronización', [
                'mensaje' => $e->getMessage(),
                'archivo' => $e->getFile(),
                'linea' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'linea' => $e->getLine()
            ], 500);
        }
    }

    protected function ejecutarPaso($nombre, $callback)
    {
        try {
            $callback();
        } catch (\Exception $e) {
            throw new \Exception("❌ Error en [$nombre]: " . $e->getMessage());
        }
    }

    /* =========================
     * TABLAS BASE
     * ========================= */

    protected function paises()
    {
        DB::connection('mysql2')->table('paises')
            ->chunkById(500, function ($rows) {

                $data = [];

                foreach ($rows as $r) {
                    $data[] = [
                        'id' => $r->id,
                        'nombre' => $r->nombre
                    ];
                }

                DB::table('paises')->upsert($data, ['id']);
            });
    }

    protected function editoriales()
    {
        DB::connection('mysql2')->table('editoriales')
            ->chunkById(500, function ($rows) {

                $data = [];

                foreach ($rows as $r) {
                    $data[] = [
                        'id' => $r->id,
                        'tipo_documento' => $r->identidad_tipo_id,
                        'nro_documento' => $r->nro_documento,
                        'nombre' => $r->nombre,
                        'responsable' => $r->responsable,
                        'telefono' => $r->telefono,
                        'correo' => $r->correo,
                        'direccion' => $r->direccion,
                        'web' => $r->web,
                        'pais' => $r->pais_id,
                        'estado' => $r->estado
                    ];
                }

                DB::table('editoriales')->upsert($data, ['id']);
            });
    }

    protected function autores()
    {
        DB::connection('mysql2')->table('autores')
            ->chunkById(500, function ($rows) {

                $data = [];

                foreach ($rows as $r) {
                    $data[] = [
                        'id' => $r->id,
                        'nombres' => $r->nombre,
                        'apellidos' => $r->apaterno . ' ' . $r->amaterno,
                        'pais' => $r->pais_id,
                        'estado' => $r->estado
                    ];
                }

                DB::table('autores')->upsert($data, ['id']);
            });
    }

    protected function materias()
    {
        DB::connection('mysql2')->table('materias')
            ->chunkById(500, function ($rows) {

                $data = [];

                foreach ($rows as $r) {
                    $data[] = [
                        'id' => $r->id,
                        'codigo' => $r->codigo,
                        'abreviatura' => $r->abreviatura,
                        'nombre' => $r->nombre
                    ];
                }

                DB::table('materias')->upsert($data, ['id']);
            });
    }

    protected function carreras()
    {
        DB::connection('mysql2')->table('carreras')
            ->chunkById(500, function ($rows) {

                $data = [];

                foreach ($rows as $r) {
                    $data[] = [
                        'id' => $r->id,
                        'nombre' => $r->nombre,
                        'codigo' => $r->codigo
                    ];
                }

                DB::table('carreras')->upsert($data, ['id']);
            });
    }

    /* =========================
     * PERSONAS + USUARIOS
     * ========================= */

    protected function personas()
    {
        DB::connection('mysql2')->table('personas')
            ->chunkById(500, function ($rows) {

                $data = [];

                foreach ($rows as $r) {
                    $data[] = [
                        'id' => $r->id,
                        'dni' => $r->nro_documento,
                        'nombres' => $r->nombre,
                        'apellido_paterno' => $r->apaterno,
                        'apellido_materno' => $r->amaterno,
                        'email_personal' => $r->correo
                    ];
                }

                DB::table('personas')->upsert($data, ['id']);
            });
    }

    protected function usuarios()
    {
        DB::connection('mysql2')->table('users')
            ->chunkById(500, function ($rows) {

                foreach ($rows as $r) {
                    // Insertar o actualizar usuario en la nueva base
                    DB::table('users')->upsert([
                        'id'         => $r->id,
                        'name'       => $r->nombre,
                        'email'      => $r->email,
                        'password'   => $r->password,
                        'estado'     => $r->estado,
                        'persona_id' => $r->persona_id,
                    ], ['id']);

                    // Verificar si ya tiene rol asignado en la nueva base
                    $existeRol = DB::table('usuario_rol_bibliotecas')
                        ->where('user_id', $r->id)
                        ->exists();

                    if (!$existeRol) {
                        DB::table('usuario_rol_bibliotecas')->insert([
                            'user_id'       => $r->id,
                            'rol_id'        => !empty($r->rol_id) ? $r->rol_id : 5, // lector por defecto
                            'biblioteca_id' => $r->biblioteca_id ?? null,
                            'estado'        => $r->estado,
                            'created_at'    => now(),
                            'updated_at'    => now(),
                        ]);
                    }
                }
            });
    }


    /* ========================= 
     * LIBROS
     * ========================= */
    protected function libros()
    {
        DB::connection('mysql2')->table('registros')
            ->orderBy('id')
            ->chunk(300, function ($rows) {

                // 🔥 Obtener IDs únicos
                $editorialesIds = collect($rows)->pluck('editorial_id')->filter()->unique();
                $archivosIds    = collect($rows)->pluck('archivo_id')->filter()->unique();

                // 🔥 Cargar en memoria
                $editoriales = DB::table('editoriales')
                    ->whereIn('id', $editorialesIds)
                    ->pluck('id')
                    ->toArray();

                $archivos = DB::connection('mysql2')->table('archivos')
                    ->whereIn('id', $archivosIds)
                    ->get()
                    ->keyBy('id');

                $data = [];

                foreach ($rows as $r) {
                    try {
                        // 🔥 Buscar imagen asociada al registro
                        $imagen = DB::connection('mysql2')->table('registro_archivos')
                            ->where('registro_id', $r->id)
                            ->first();

                        $imagen_libro = $imagen
                            ? DB::connection('mysql2')->table('archivos')->find($imagen->archivo_id)
                            : null;

                        // 🔥 LIMPIAR editorial
                        $editorial_id = !empty($r->editorial_id) ? $r->editorial_id : null;

                        if (!is_null($editorial_id) && !in_array($editorial_id, $editoriales)) {
                            throw new \Exception("Editorial no existe ID: " . $editorial_id);
                        }

                        // 🔥 ARCHIVO SEGURO
                        $archivo = $archivos[$r->archivo_id] ?? null;

                        // 🔥 LIMPIAR RUTAS (eliminar "archivos/")
                        $ruta_archivo = $archivo ? str_replace('archivos/', '', $archivo->ruta) : null;
                        $ruta_imagen  = $imagen_libro ? str_replace('archivos/', '', $imagen_libro->ruta) : null;

                        $data[] = [
                            'id'              => $r->id,
                            'codigo_ant'      => $r->codigo ?? 'LIB-' . $r->id,
                            'idioma'          => $r->idioma_id ?? null,
                            'edicion'         => $r->edicion,
                            'anio_edicion'    => $r->edicion_year,
                            'paginas'         => $r->paginas,
                            'tipo_registro_id'=> $r->registro_tipo_id,
                            'palabras_clave'  => $r->palabras_clave,
                            'titulo'          => $r->titulo,
                            'isbn'            => $r->isbn,
                            'imagen'          => $ruta_imagen ? 'storage/libros/' . $ruta_imagen : null,
                            'archivo_indice'  => $ruta_archivo ? 'storage/indices/' . $ruta_archivo : null,
                            'editorial_id'    => $editorial_id,
                        ];

                    } catch (\Exception $e) {
                        throw new \Exception("Libro ID {$r->id}: " . $e->getMessage());
                    }
                }

                // 🔥 INSERT MASIVO
                if (!empty($data)) {
                    DB::table('libros')->upsert($data, ['id']);
                }
            });
    }

    protected function libro_relaciones()
    {
        // materias
        DB::connection('mysql2')->table('registro_materias')
            ->chunkById(500, function ($rows) {

                $data = [];

                foreach ($rows as $r) {
                    $data[] = [
                        'libro_id' => $r->registro_id,
                        'materia_id' => $r->materia_id
                    ];
                }

                DB::table('libro_materias')->upsert($data, ['libro_id','materia_id']);

            }, 'registro_id'); // 🔥 IMPORTANTE

        // autores
        DB::connection('mysql2')->table('registro_autores')
            ->chunkById(500, function ($rows) {

                $data = [];

                foreach ($rows as $r) {
                    $data[] = [
                        'libro_id' => $r->registro_id,
                        'autor_id' => $r->autor_id
                    ];
                }

                DB::table('autor_libros')->upsert($data, ['libro_id','autor_id']);

            }, 'registro_id'); // 🔥 IMPORTANTE
    }

    /* =========================
     * COMPRAS + EJEMPLARES
     * ========================= */

    protected function compras()
    {
        DB::connection('mysql2')->table('adquisiciones')
            ->chunkById(500, function ($rows) {

                $data = [];

                foreach ($rows as $r) {
                    $data[] = [
                        'id' => $r->id,
                        'codigo' => $r->codigo,
                        'fecha_compra' => $r->fecha
                    ];
                }

                DB::table('compras')->upsert($data, ['id']);
            });
    }

    protected function ejemplares()
    {
        DB::connection('mysql2')->table('ejemplares')
            ->chunkById(500, function ($rows) {

                $data = [];

                foreach ($rows as $r) {
                    $data[] = [
                        'codigo_interno' => $r->nro_ejemplar,
                        'codigo_ant' => $r->codigo,
                        'libro_id' => $r->registro_id,
                        'biblioteca_id' => $r->biblioteca_id,
                        'adquisicion' => $r->adquisicion_id,
                        'tipo' => 'eje.',
                        'estado' => $r->estado
                    ];
                }

                DB::table('ejemplares')->upsert($data, ['codigo_interno']);
            });
    }
}