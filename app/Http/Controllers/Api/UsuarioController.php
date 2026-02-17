<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

use App\Models\User;
use App\Models\Persona;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{   
    public function listar(Request $request)
    {
       
        $query = User::with('roles','persona');

        return DataTables::of($query)
            ->addColumn('acciones', function($row) {
                $btns = '<button class="btn btn-sm btn-primary me-1 editarUsuario">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" /><path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" /><line x1="16" y1="5" x2="19" y2="8" /></svg>
                </button>';
                $btns .= '<button class="btn btn-sm btn-warning me-1 cambiarContrasena">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><rect x="5" y="11" width="14" height="10" rx="2" /><circle cx="12" cy="16" r="1" /><path d="M8 11v-4a4 4 0 0 1 8 0v4" /></svg>
                </button>';
                $btns .= '<button class="btn btn-sm btn-danger me-1 eliminarUsuario">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-trash-x"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7h16" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /><path d="M10 12l4 4m0 -4l-4 4" /></svg>
                </button>';
                return $btns;
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
    public function editar(Request $request)
    {
        $request->validate([
            // PERSONA
            'dni'               => 'required|string|max:15|unique:personas,dni',
            'nombres'           => 'required|string|max:150',
            'apellido_paterno'  => 'required|string|max:150',
            'apellido_materno'  => 'nullable|string|max:150',
            'sexo'              => 'nullable|in:M,F,O',
            'telefono'          => 'nullable|string|max:20',
        ]);
        //return $request;   
        $user=User::where('persona_id',$request->id)->first(); 
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

            $user->roles()->sync($request->roles);

            // 👉 OPCIÓN B: usuario_rol_bibliotecas
            Usuario_rol_biblioteca::where('user_id', $user->id)->delete();
            foreach ($request->roles as $rolId) {
                DB::table('usuario_rol_bibliotecas')->insert([
                    'user_id'       => $user->id,
                    'rol_id'        => $rolId,
                    'biblioteca_id'=> 1, // o dinámico
                    'activo'        => true,
                ]);
            }

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
}
