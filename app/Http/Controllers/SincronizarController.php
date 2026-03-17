<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use App\Models\User;
use App\Models\Pais;
use App\Models\Libro;
use App\Models\Tipo_registro;
use App\Models\Idioma;
use App\Models\Persona;
use App\Models\Editorial;
use App\Models\Ejemplar;
use App\Models\Autor;
use App\Models\Materia;
use App\Models\Carrera;
use App\Models\Compra;
use App\Models\Usuario_rol_biblioteca;
use App\Models\Libro_materia;
use App\Models\Autor_libro;

class SincronizarController extends Controller
{
    public function sincronizar()
    {
        DB::beginTransaction();

        try {
            $this->personas_usuarios();
            $this->paises();
            $this->autores();
            $this->materias();
            $this->carreras();
            $this->editoriales();
            $this->registros();
            $this->compras();
            $this->ejemplares();

            DB::commit();

            return "✅ Sincronización completada";
        } catch (\Exception $e) {
            DB::rollBack();
            return "❌ Error: " . $e->getMessage();
        }
    }

    protected function personas_usuarios()
    {
        $personas = DB::connection('mysql2')->table('personas')->get();

        foreach ($personas as $persona) {

            // evitar duplicados
            if (Persona::where('id', $persona->id)->exists()) continue;

            $temp_persona = new Persona;
            $temp_persona->id = $persona->id;
            $temp_persona->dni = $persona->nro_documento;
            $temp_persona->nombres = $persona->nombre;
            $temp_persona->apellido_paterno = $persona->apaterno;
            $temp_persona->apellido_materno = $persona->amaterno;
            $temp_persona->fecha_nacimiento = $persona->nacimiento;
            $temp_persona->sexo = $persona->sexo;
            $temp_persona->direccion = $persona->direccion;
            $temp_persona->telefono = $persona->telefono;
            $temp_persona->email_personal = $persona->correo;
            $temp_persona->save();

            // 🔥 traer usuario relacionado correctamente
            $usuario = DB::connection('mysql2')
                ->table('users')
                ->where('persona_id', $persona->id)
                ->first();

            if ($usuario) {

                $rol = DB::connection('mysql2')
                    ->table('roles')
                    ->where('id', $usuario->rol_id)
                    ->first();

                if (!User::where('id', $usuario->id)->exists()) {

                    $temp_usuario = new User;
                    $temp_usuario->id = $usuario->id;
                    $temp_usuario->name = $usuario->nombre;
                    $temp_usuario->email = $usuario->email;
                    $temp_usuario->password = $usuario->password; // ya viene hasheado
                    $temp_usuario->estado = $usuario->estado;
                    $temp_usuario->persona_id = $usuario->persona_id;
                    $temp_usuario->tipo_usuario = $rol->nombre ?? null;
                    $temp_usuario->save();

                    Usuario_rol_biblioteca::create([
                        'user_id' => $usuario->id,
                        'rol_id' => $usuario->rol_id
                    ]);
                }

                $temp_persona->codigo = $usuario->codigo;
                $temp_persona->save();
            }
        }
    }

    protected function paises()
    {
        $paises = DB::connection('mysql2')->table('paises')->get();

        foreach ($paises as $pais) {
            Pais::updateOrCreate(
                ['id' => $pais->id],
                ['nombre' => $pais->nombre]
            );
        }
    }

    protected function autores()
    {
        $autores = DB::connection('mysql2')->table('autores')->get();

        foreach ($autores as $autor) {
            Autor::updateOrCreate(
                ['id' => $autor->id],
                [
                    'nombres' => $autor->nombre,
                    'apellidos' => $autor->apaterno . ' ' . $autor->amaterno,
                    'pais' => $autor->pais_id,
                    'estado' => $autor->estado
                ]
            );
        }
    }

    protected function materias()
    {
        $materias = DB::connection('mysql2')->table('materias')->get();

        foreach ($materias as $materia) {
            Materia::updateOrCreate(
                ['id' => $materia->id],
                (array) $materia
            );
        }
    }

    protected function carreras()
    {
        $carreras = DB::connection('mysql2')->table('carreras')->get();

        foreach ($carreras as $carrera) {
            Carrera::updateOrCreate(
                ['id' => $carrera->id],
                (array) $carrera
            );
        }
    }

    protected function editoriales()
    {
        $editoriales = DB::connection('mysql2')->table('editoriales')->get();

        foreach ($editoriales as $editorial) {
            Editorial::updateOrCreate(
                ['id' => $editorial->id],
                (array) $editorial
            );
        }
    }

    protected function registros()
    {
        $registros = DB::connection('mysql2')->table('registros')->get();

        foreach ($registros as $registro) {

            if (Libro::where('id', $registro->id)->exists()) continue;

            $temp = new Libro;
            $temp->id = $registro->id;
            $temp->titulo = $registro->titulo;
            $temp->isbn = $registro->isbn;
            $temp->editorial_id = $registro->editorial_id;

            // imagen segura
            $registro_archivo = DB::connection('mysql2')
                ->table('registro_archivos')
                ->where('registro_id', $registro->id)
                ->where('principal', 1)
                ->first();

            if ($registro_archivo) {
                $archivo = DB::connection('mysql2')
                    ->table('archivos')
                    ->where('id', $registro_archivo->archivo_id)
                    ->first();

                $temp->imagen = $archivo->ruta ?? null;
            }

            $temp->save();

            // materias
            $materias = DB::connection('mysql2')
                ->table('registro_materias')
                ->where('registro_id', $registro->id)
                ->get();

            foreach ($materias as $m) {
                Libro_materia::firstOrCreate([
                    'libro_id' => $m->registro_id,
                    'materia_id' => $m->materia_id
                ]);
            }

            // autores
            $autores = DB::connection('mysql2')
                ->table('registro_autores')
                ->where('registro_id', $registro->id)
                ->get();

            foreach ($autores as $a) {
                Autor_libro::firstOrCreate([
                    'libro_id' => $a->registro_id,
                    'autor_id' => $a->autor_id
                ]);
            }
        }
    }

    protected function compras()
    {
        $compras = DB::connection('mysql2')->table('adquisiciones')->get();

        foreach ($compras as $c) {
            Compra::updateOrCreate(
                ['id' => $c->id],
                [
                    'codigo' => $c->codigo,
                    'fecha_compra' => $c->fecha,
                    'proveedor_id' => $c->proveedor_id,
                    'usuario_id' => $c->user_id,
                    'observaciones' => $c->observaciones,
                    'year' => $c->year
                ]
            );
        }
    }

    protected function ejemplares()
    {
        $ejemplares = DB::connection('mysql2')->table('ejemplares')->get();

        foreach ($ejemplares as $e) {
            Ejemplar::updateOrCreate(
                ['codigo_interno' => $e->nro_ejemplar],
                [
                    'codigo_ant' => $e->codigo,
                    'tipo' => 'eje.',
                    'libro_id' => $e->registro_id,
                    'biblioteca_id' => $e->biblioteca_id,
                    'estado' => $e->estado
                ]
            );
        }
    }
}