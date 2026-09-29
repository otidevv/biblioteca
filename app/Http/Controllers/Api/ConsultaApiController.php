<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EstudianteApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ConsultaApiController extends Controller
{
    public function consulta_api(Request $request)
    {
        // El numero se inserta en la URL del servicio externo: solo se aceptan alfanumericos.
        $request->validate([
            'nro_documento' => ['required', 'string', 'regex:/^[A-Za-z0-9]{6,15}$/'],
        ]);

        $consulta = $request->tipo_usuario == "ESTUDIANTE" ? 1 : ($request->tipo_usuario == "DOCENTE" ? 2 : ($request->tipo_usuario == "ADMINISTRATIVO" ? 3 : ($request->tipo_usuario == "EXTERNO" ? 4 : 0)));

        if ($consulta == 1) {
            return $this->consultarEstudiante($request->nro_documento);
        }

        if ($consulta == 2) {
            return $this->consultarTeacher($request->nro_documento);
        }

        if ($consulta == 3) {
            return $this->consultarExterno($request->nro_documento);
        }

        if ($consulta == 4) {
            return $this->consultarExterno($request->nro_documento);
        }

        return response()->json(['message' => 'Tipo de usuario no valido.'], 400);
    }

    public function consultarTeacher($dni)
    {
        $token = config('services.unamad_integrations.teacher_token');
        $verifySsl = config('services.unamad_integrations.verify_ssl', true);
        $baseUrl = rtrim((string) config('services.unamad_integrations.teacher_url', 'https://daa-documentos.unamad.edu.pe:8081/api/data/teacher'), '/');
        $url = $baseUrl . '/' . rawurlencode((string) $dni);

        if (!$token) {
            return response()->json([
                'message' => 'La integracion institucional de docentes no esta configurada.',
            ], 503);
        }

        try {
            $response = Http::timeout(10)
                ->withOptions(['verify' => $verifySsl])
                ->withToken($token)
                ->acceptJson()
                ->get($url);

            if (!$response->successful()) {
                return response()->json([
                    'message' => 'Error al consultar el docente.',
                    'codigo_http' => $response->status(),
                ], 500);
            }

            $datos = $response->json();

            if (empty($datos) || !isset($datos['name'])) {
                return response()->json([
                    'message' => 'No se encontraron datos del docente.',
                ], 404);
            }

            $respuesta = [
                'codigo' => $datos['CODIGO'] ?? '',
                'nombre' => $datos['name'],
                'apaterno' => $datos['paternalSurname'] ?? '',
                'amaterno' => $datos['maternalSurname'] ?? '',
                'correo' => $datos['email'] ?? '',
                'correo_institucional' => '',
            ];

            return response()->json([
                'message' => 'Datos encontrados',
                'respuesta' => $respuesta,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error de conexion con el servicio externo.',
            ], 500);
        }
    }

    public function consultarExterno($dni)
    {
        $verifySsl = config('services.unamad_integrations.verify_ssl', true);
        $url = 'https://apidatos.unamad.edu.pe/api/consulta/' . rawurlencode((string) $dni);

        try {
            $response = Http::timeout(10)
                ->withOptions(['verify' => $verifySsl])
                ->get($url);

            if ($response->successful()) {
                $datos = $response->json();

                if (empty($datos) || !isset($datos['NOMBRES'])) {
                    return response()->json(['message' => 'No se encontraron datos del externo.'], 404);
                }

                $respuesta = [
                    'codigo' => '',
                    'nombre' => $datos['NOMBRES'],
                    'apaterno' => $datos['AP_PAT'],
                    'amaterno' => $datos['AP_MAT'],
                    'correo' => '',
                    'correo_institucional' => '',
                ];

                return response()->json(['message' => 'Datos encontrados', 'respuesta' => $respuesta], 200);
            }

            return response()->json([
                'message' => 'No se pudo consultar el externo.',
                'codigo_http' => $response->status(),
            ], 500);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error de conexion con el servicio externo.',
            ], 500);
        }
    }

    public function consultarEstudiante($dni)
    {
        $resultado = app(EstudianteApiService::class)->buscarPorDni((string) $dni);

        if (! $resultado['ok']) {
            return response()->json(
                array_filter(['message' => $resultado['message'], 'codigo_http' => $resultado['codigo_http'] ?? null]),
                $resultado['status']
            );
        }

        $info = $resultado['info'];

        return response()->json([
            'message' => 'Datos encontrados',
            'respuesta' => [
                'codigo' => $info['codigo'],
                'nombre' => $info['nombres'],
                'apaterno' => $info['apellido_paterno'],
                'amaterno' => $info['apellido_materno'],
                'correo' => $info['email'],
                'correo_institucional' => $info['email'],
            ],
        ], 200);
    }
}
