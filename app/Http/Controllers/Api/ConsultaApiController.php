<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ConsultaApiController extends Controller
{
    public function consulta_api(Request $request)
    {
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
        $url = "{$baseUrl}/{$dni}";

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
                    'datos' => $datos,
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
                'message' => 'Error de conexion: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function consultarExterno($dni)
    {
        $verifySsl = config('services.unamad_integrations.verify_ssl', true);
        $url = "https://apidatos.unamad.edu.pe/api/consulta/{$dni}";

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
                'message' => 'Error de conexion: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function consultarEstudiante($dni)
    {
        $token = config('services.unamad_integrations.student_token');
        $verifySsl = config('services.unamad_integrations.verify_ssl', true);
        $baseUrl = rtrim((string) config('services.unamad_integrations.student_url', 'https://daa-documentos.unamad.edu.pe:8081/api/data/student'), '/');
        $url = "{$baseUrl}/{$dni}";

        if (!$token) {
            return response()->json([
                'message' => 'La integracion institucional de estudiantes no esta configurada.',
            ], 503);
        }

        try {
            $response = Http::timeout(30)
                ->withOptions(['verify' => $verifySsl])
                ->withToken($token)
                ->acceptJson()
                ->get($url);

            if (!$response->successful()) {
                return response()->json([
                    'message' => 'Error al consultar el estudiante.',
                    'codigo_http' => $response->status(),
                ], 500);
            }

            $datos = $response->json();

            if (empty($datos) || !isset($datos['status']) || $datos['status'] !== 'success') {
                return response()->json([
                    'message' => 'No se encontraron datos del estudiante.',
                    'datos' => $datos,
                ], 404);
            }

            if (empty($datos['data']) || !isset($datos['data'][0]['info'])) {
                return response()->json([
                    'message' => 'No se encontraron datos del estudiante.',
                    'datos' => $datos,
                ], 404);
            }

            $info = $datos['data'][0]['info'];

            $respuesta = [
                'codigo' => $info['username'] ?? '',
                'nombre' => $info['name'] ?? '',
                'apaterno' => $info['paternalSurname'] ?? '',
                'amaterno' => $info['maternalSurname'] ?? '',
                'correo' => $info['email'] ?? '',
                'correo_institucional' => $info['email'] ?? '',
            ];

            return response()->json([
                'message' => 'Datos encontrados',
                'respuesta' => $respuesta,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error de conexion: ' . $e->getMessage(),
            ], 500);
        }
    }
}
