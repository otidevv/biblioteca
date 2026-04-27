<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

use App\Models\User;
use App\Models\Persona;
use App\Models\Usuario_rol_biblioteca;
use App\Services\LectorImportService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    private function construirNombreUsuario(Request $request): string
    {
        return trim(implode(' ', array_filter([
            $request->nombres,
            $request->apellido_paterno,
            $request->apellido_materno,
        ])));
    }

    // ===================USUARIOS ADMINISTRACIÓN============================
    public function listar(Request $request)
    {
            $query = User::with(['roles', 'persona']);

    if ($request->filled('tipo_usuario')) {

        // 👉 Filtrar SOLO por el rol seleccionado
        $query->whereHas('roles', function ($q) use ($request) {
            $q->where('rol_id', $request->tipo_usuario);
        });

    } else {

        // 👉 Listado general (roles administrativos)
        $query->whereHas('roles', function ($q) {
            $q->whereIn('rol_id', [1, 2, 3, 4]);
        });

    }


        return DataTables::of($query)
            ->orderColumn('rol', function ($query, $order) {
                $direction = strtolower($order) === 'desc' ? 'desc' : 'asc';

                $query->orderByRaw("
                    (
                        select min(roles.nombre)
                        from roles
                        inner join usuario_rol_bibliotecas
                            on roles.id = usuario_rol_bibliotecas.rol_id
                        where usuario_rol_bibliotecas.user_id = users.id
                    ) {$direction}
                ");
            })
            ->filterColumn('rol', function ($query, $keyword) {
                $search = mb_strtolower(trim($keyword));

                if ($search === '') {
                    return;
                }

                $query->whereHas('roles', function ($roleQuery) use ($search) {
                    $roleQuery->whereRaw('LOWER(roles.nombre) LIKE ?', ["%{$search}%"]);
                });
            })
            ->addColumn('acciones', function ($row) {

                return '
                    <button class="btn btn-sm btn-primary me-1 editarUsuario">✏️</button>
                    <button class="btn btn-sm btn-warning me-1 cambiarContrasena">🔒</button>
                    <button class="btn btn-sm btn-danger me-1 eliminarUsuario">🗑️</button>
                ';
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }

    public function nuevo(Request $request)
    {
        $request->validate([
            // PERSONA
            'dni'               => 'required|string|max:15|unique:personas,dni',
            'nombres'           => 'required|string|max:150',
            'apellido_paterno'  => 'required|string|max:150',
            'apellido_materno'  => 'nullable|string|max:150',
            'sexo'              => 'nullable|in:M,F,O',
            'biblioteca'        => 'nullable|integer|exists:bibliotecas,id',
            'telefono'          => 'required|string|max:20',
            'direccion'         => 'required|string|max:255',
            'correo'            => 'required|email|unique:users,email',

            // USUARIO
            'password'          => 'required|string|min:8',
            'roles'             => 'required|array|min:1',
            'roles.*'           => 'exists:roles,id',
        ]);
        //return $request;    
        DB::beginTransaction();

        try {

            /** =========================
             *  PERSONA
             *  ========================= */
            $persona = Persona::create([
                'dni'               => $request->dni,
                'nombres'           => $request->nombres,
                'apellido_paterno'  => $request->apellido_paterno,
                'apellido_materno'  => $request->apellido_materno,
                'sexo'              => $request->sexo,
                'telefono'          => $request->telefono,
                'email_personal'    => $request->correo,
                'direccion'         => $request->direccion,
                'activo'           =>1,
            ]);

            /** =========================
             *  USUARIO
             *  ========================= */
            $user = User::create([
                'name'       => $this->construirNombreUsuario($request),
                'email'      => $request->correo,
                'password'   => Hash::make($request->password),
                'estado'     => 1,
                'origen'     => 'local',
                'persona_id'=> $persona->id,
            ]);

            /** =========================
             *  ROLES
             *  ========================= */

            // 👉 OPCIÓN A: tabla pivote rol_usuarios
            $user->roles()->sync($request->roles);

            Usuario_rol_biblioteca::where('user_id', $user->id)->delete();
            foreach ($request->roles as $rolId) {
                DB::table('usuario_rol_bibliotecas')->insert([
                    'user_id'       => $user->id,
                    'rol_id'        => $rolId,
                    'biblioteca_id'=> $request->biblioteca,
                    'estado'        => 1,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }

            // 👉 OPCIÓN B: usuario_rol_bibliotecas
            /*
            foreach ($request->roles as $rolId) {
                DB::table('usuario_rol_bibliotecas')->insert([
                    'user_id'       => $user->id,
                    'rol_id'        => $rolId,
                    'biblioteca_id'=> 1, // o dinámico
                    'activo'        => true,
                ]);
            }
            */

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Usuario registrado correctamente'
            ], 201);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al registrar usuario',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    public function edit(Request $request)
    {
        $user = User::findOrFail($request->id);
        $persona = Persona::findOrFail($user->persona_id);

        $request->validate([
            // PERSONA
            'id'               => 'required|exists:users,id',
            'dni'              => [
                'required',
                'string',
                'max:15',
                Rule::unique('personas', 'dni')->ignore($persona->id),
            ],
            'nombres'           => 'required|string|max:150',
            'apellido_paterno'  => 'required|string|max:150',
            'apellido_materno'  => 'nullable|string|max:150',
            'sexo'              => 'nullable|in:M,F,O',
            'telefono'          => 'required|string|max:20',
            'direccion'         => 'required|string|max:255',
            'biblioteca'        => 'nullable|integer|exists:bibliotecas,id',
            'roles'             => 'required|array|min:1',
            'roles.*'           => 'exists:roles,id',
        ]);

        DB::beginTransaction();
        try {

            /** =========================
             *  PERSONA
             *  ========================= */
            $persona->dni=$request->dni;
            $persona->nombres=$request->nombres;
            $persona->apellido_paterno=$request->apellido_paterno;
            $persona->apellido_materno=$request->apellido_materno;
            $persona->sexo=$request->sexo;
            $persona->telefono=$request->telefono;
            $persona->direccion=$request->direccion;
            $persona->save();
            $user->name = $this->construirNombreUsuario($request);
            $user->save();
            $user->roles()->sync($request->roles);

            // 👉 OPCIÓN B: usuario_rol_bibliotecas
            Usuario_rol_biblioteca::where('user_id', $request->id)->delete();
            foreach ($request->roles as $rolId) {
                DB::table('usuario_rol_bibliotecas')->insert([
                    'user_id'       => $request->id,
                    'rol_id'        => $rolId,
                    'biblioteca_id'=> $request->biblioteca, // o dinámico
                    'estado'        => 1,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Usuario actualizado correctamente'
            ], 201);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar usuario',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    public function cambiarContrasena(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:users,id',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::findOrFail($request->id);
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Contraseña actualizada correctamente'
        ]);
    }
    // ===================LECTORES LECTORES============================
    public function listarLectores(Request $request)
    {
        $query = User::with('roles','persona')
            ->whereHas('roles', function($q) {
                $q->where('rol_id', '5'); // ID del rol "Lector"
            });
        return DataTables::of($query)
            ->addColumn('acciones', function($row) {
                return '
                    <div class="dropdown admin-action-menu">
                        <button class="btn admin-action-menu__trigger" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Abrir acciones">
                            <i class="bi bi-three-dots"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end admin-action-menu__dropdown">
                            <button class="dropdown-item admin-action-link admin-action-link--edit editarLector" type="button">
                                <i class="bi bi-pencil-square"></i><span>Editar</span>
                            </button>
                        </div>
                    </div>
                ';
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }
    public function nuevoLector(Request $request)
    {
        $request->validate([
            // PERSONA
            'dni'               => 'required|string|max:15|unique:personas,dni',
            'tipo_persona'      => 'required|in:ESTUDIANTE,DOCENTE,ADMINISTRATIVO,EXTERNO',
            'nombres'           => 'required|string|max:150',
            'apellido_paterno'  => 'required|string|max:150',
            'apellido_materno'  => 'nullable|string|max:150',
            'sexo'              => 'nullable|in:M,F,O',
            'telefono'          => 'required|string|max:20',
            'email_personal'    => 'required|email|unique:users,email',
            'direccion'         => 'nullable|string|max:255',
            'codigo_institucional' => [
                'exclude_unless:tipo_persona,ESTUDIANTE',
                'required',
                'string',
                'max:255',
                'unique:personas,codigo_institucional',
            ],
            'carrera_id'        => [
                'exclude_unless:tipo_persona,ESTUDIANTE',
                'required',
                'integer',
                'exists:carreras,id',
            ],
            'estado_academico'  => [
                'exclude_unless:tipo_persona,ESTUDIANTE',
                'required',
                'string',
                'max:255',
            ],

            // USUARIO
            'password'          => 'required|confirmed|min:8',
        ]);
        //return $request;    
        DB::beginTransaction();

        try {

            /** =========================
             *  PERSONA
             *  ========================= */
            $persona = Persona::create([
                'dni'               => $request->dni,
                'tipo_persona'      => $request->tipo_persona,
                'nombres'           => $request->nombres,
                'apellido_paterno'  => $request->apellido_paterno,
                'apellido_materno'  => $request->apellido_materno,
                'sexo'              => $request->sexo,
                'telefono'          => $request->telefono,
                'email_personal'    => $request->email_personal,
                'direccion'         => $request->direccion,
                'codigo_institucional' => $request->codigo_institucional,
                'carrera_id'        => $request->filled('carrera_id') && $request->carrera_id != 0 ? $request->carrera_id : null,
                'estado_academico'  => $request->filled('estado_academico') && $request->estado_academico != 0 ? $request->estado_academico : null,
            ]);

            /** =========================
             *  USUARIO
             *  ========================= */
            $user = User::create([
                'name'       => $this->construirNombreUsuario($request),
                'email'      => $request->email_personal,
                'password'   => Hash::make($request->password),
                'estado'     => 1,
                'origen'     => 'local',
                'tipo_usuario'     => 'Lector',
                'persona_id'=> $persona->id,
            ]);

            /** =========================
             *  ROLES
             *  ========================= */

            // 👉 OPCIÓN A: tabla pivote rol_usuarios
            $user->roles()->sync([5]); // Asignar rol "Lector" (ID=3)

            DB::commit();

            $mailSent = true;

            try {
                Mail::send('emails.lector_bienvenida', [
                    'nombre' => $user->name,
                    'correo' => $user->email,
                    'passwordTemporal' => $request->password,
                ], function ($message) use ($user) {
                    $message->to($user->email, $user->name)
                        ->subject('Bienvenido al Sistema de Biblioteca');
                });
            } catch (\Throwable $mailException) {
                $mailSent = false;
                Log::warning('No se pudo enviar el correo de bienvenida al lector.', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $mailException->getMessage(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => $mailSent
                    ? 'Lector registrado correctamente y correo de bienvenida enviado.'
                    : 'Lector registrado correctamente, pero no se pudo enviar el correo de bienvenida.',
                'mail_sent' => $mailSent,
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->jsonServerError('Error al registrar el lector.', $e, 500, [
                'action' => 'nuevoLector',
            ]);
        }
    }
    public function editLector(Request $request)
    {
        $user = User::findOrFail($request->id);
        $persona = Persona::findOrFail($user->persona_id);

        $request->validate([
            'id'                => 'required|exists:users,id',
            'dni'               => [
                'required',
                'string',
                'max:15',
                Rule::unique('personas', 'dni')->ignore($persona->id),
            ],
            'tipo_persona'      => 'required|in:ESTUDIANTE,DOCENTE,ADMINISTRATIVO,EXTERNO',
            'nombres'           => 'required|string|max:150',
            'apellido_paterno'  => 'required|string|max:150',
            'apellido_materno'  => 'nullable|string|max:150',
            'sexo'              => 'nullable|in:M,F,O',
            'telefono'          => 'required|string|max:20',
            'email_personal'    => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'direccion'         => 'nullable|string|max:255',
            'codigo_institucional' => [
                'exclude_unless:tipo_persona,ESTUDIANTE',
                'required',
                'string',
                'max:255',
                Rule::unique('personas', 'codigo_institucional')->ignore($persona->id),
            ],
            'carrera_id'        => [
                'exclude_unless:tipo_persona,ESTUDIANTE',
                'required',
                'integer',
                'exists:carreras,id',
            ],
            'estado_academico'  => [
                'exclude_unless:tipo_persona,ESTUDIANTE',
                'required',
                'string',
                'max:255',
            ],
        ]);

        DB::beginTransaction();

        try {
            $persona->update([
                'dni' => $request->dni,
                'tipo_persona' => $request->tipo_persona,
                'nombres' => $request->nombres,
                'apellido_paterno' => $request->apellido_paterno,
                'apellido_materno' => $request->apellido_materno,
                'sexo' => $request->sexo,
                'telefono' => $request->telefono,
                'email_personal' => $request->email_personal,
                'direccion' => $request->direccion,
                'codigo_institucional' => $request->codigo_institucional,
                'carrera_id' => $request->filled('carrera_id') && $request->carrera_id != 0 ? $request->carrera_id : null,
                'estado_academico' => $request->filled('estado_academico') && $request->estado_academico != 0 ? $request->estado_academico : null,
            ]);

            $user->update([
                'name' => $this->construirNombreUsuario($request),
                'email' => $request->email_personal,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Lector actualizado correctamente'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->jsonServerError('Error al actualizar el lector.', $e, 500, [
                'action' => 'editLector',
                'user_id' => $request->id,
            ]);
        }
    }

    public function previewImportacionLectores(Request $request, LectorImportService $service)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,csv,txt|max:5120',
        ]);

        try {
            return response()->json([
                'success' => true,
                'message' => 'Archivo procesado correctamente. Revisa la vista previa antes de importar.',
                'data' => $service->preview($request->file('archivo')),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Error al previsualizar importacion de lectores.', [
                'exception' => get_class($e),
                'exception_message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo procesar la previsualizacion del archivo.',
            ], 422);
        }
    }

    public function cargarImportacionLectores(Request $request, LectorImportService $service)
    {
        $request->validate([
            'token' => 'required|string',
            'rows' => 'nullable|array',
        ]);

        try {
            $preview = null;

            if ($request->filled('rows')) {
                $preview = $service->reviewTokenRows((string) $request->token, $request->input('rows', []));

                if (!$preview['can_import']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Todavia hay filas con observaciones. Corrigelas antes de importar.',
                        'data' => $preview,
                    ], 422);
                }
            }

            $resultado = $service->import((string) $request->token, $preview['rows'] ?? null);

            return response()->json([
                'success' => true,
                'message' => "Se importaron {$resultado['created']} lectores correctamente.",
                'created' => $resultado['created'],
            ]);
        } catch (\Throwable $e) {
            Log::warning('Error al cargar importacion de lectores.', [
                'exception' => get_class($e),
                'exception_message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'No se pudo completar la importacion de lectores.',
            ], 422);
        }
    }
}
