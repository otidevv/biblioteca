<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

use App\Models\User;
use App\Models\Persona;
use App\Models\Usuario_rol_biblioteca;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{   
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
            'biblioteca'        => 'nullable|string|max:20',
            'telefono'          => 'nullable|string|max:20',
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
                'activo'            => true,
            ]);

            /** =========================
             *  USUARIO
             *  ========================= */
            $user = User::create([
                'name'       => $request->nombres,
                'email'      => $request->correo,
                'password'   => Hash::make($request->password),
                'estado'     => 'activo',
                'origen'     => 'local',
                'persona_id'=> $persona->id,
            ]);

            /** =========================
             *  ROLES
             *  ========================= */

            // 👉 OPCIÓN A: tabla pivote rol_usuarios
            $user->roles()->sync($request->roles);

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
        $request->validate([
            // PERSONA
            'id'               => 'required|exists:users,id',
            'dni'               => 'required|string|max:15|',
            'nombres'           => 'required|string|max:150',
            'apellido_paterno'  => 'required|string|max:150',
            'apellido_materno'  => 'nullable|string|max:150',
            'sexo'              => 'nullable|in:M,F,O',
            'telefono'          => 'nullable|string|max:20',
            'biblioteca'        => 'nullable|string|max:20',
        ]);
        //return $request;   
        $user=User::find($request->id);           
        $persona=Persona::find($user->persona_id); 
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
            $user->roles()->sync($request->roles);

            // 👉 OPCIÓN B: usuario_rol_bibliotecas
            Usuario_rol_biblioteca::where('user_id', $request->id)->delete();
            foreach ($request->roles as $rolId) {
                DB::table('usuario_rol_bibliotecas')->insert([
                    'user_id'       => $request->id,
                    'rol_id'        => $rolId,
                    'biblioteca_id'=> $request->biblioteca, // o dinámico
                    'activo'        => true,
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
                $btns = '<button class="btn btn-sm btn-primary me-1 editarLector">
                <svg xmlns="http://www.w3.org/2000/svg" class=" icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" /><path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" /><line x1="16" y1="5" x2="19" y2="8" /></svg>
                </button>';
                return $btns;
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
            'telefono'          => 'nullable|string|max:20',
            'email_personal'    => 'required|email|unique:users,email',

            // USUARIO
            'password'          => 'required|confirmed|min:6',
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
                'email_personal'    => $request->email_personal,
                'direccion'         => $request->direccion,
            ]);

            /** =========================
             *  USUARIO
             *  ========================= */
            $user = User::create([
                'name'       => $request->nombres,
                'email'      => $request->email_personal,
                'password'   => Hash::make($request->password),
                'estado'     => 'activo',
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

            return response()->json([
                'success' => true,
                'message' => 'Lector registrado correctamente'
            ], 201);

        } catch (\Throwable $e) {

            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el lector: ' . $e->getMessage()
            ], 500);
        }
    }
}
