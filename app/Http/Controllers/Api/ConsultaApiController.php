<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class ConsultaApiController extends Controller
{
    //
    
    public function consulta_api(Request $request)
    {
        if ($request->tipo_usuario == 1) { // estudiante
            return $this->consultarEstudiante($request->nro_documento);
        }
        if ($request->tipo_usuario == 2) { // docente
            return $this->consultarTeacher($request->nro_documento);
        }
        if ($request->tipo_usuario == 3) { // administrativo
            return $this->consultarExterno($request->nro_documento);
        }
        if ($request->tipo_usuario == 4) { // externo
            return $this->consultarExterno($request->nro_documento);
        }

        return response()->json(['message' => 'Tipo de usuario no válido.'], 400);
    }
    public function consultarTeacher($dni)
    {
        $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJqdGkiOiI3MjE4NzM0OC02OWQ1LTRjOGEtOTA2MC0zNzJiOTc3NzZiOTEiLCJodHRwOi8vc2NoZW1hcy54bWxzb2FwLm9yZy93cy8yMDA1LzA1L2lkZW50aXR5L2NsYWltcy9uYW1lIjoiZWhvbGdhZG8iLCJuYW1lIjoiZWhvbGdhZG8iLCJodHRwOi8vc2NoZW1hcy5taWNyb3NvZnQuY29tL3dzLzIwMDgvMDYvaWRlbnRpdHkvY2xhaW1zL3JvbGUiOiJBcGlDb25zdW1lciIsImV4cCI6MTc1Mjc4MjY5MSwiaXNzIjoiYzk4NGRmYjFhMDE3YTNlZjhiOTdlMjUzOWY3ZWNhYWEifQ.LI1iiBp3_aO25ab6qIGqeki-knEz-WgOfuiN8j8P4vY';
        $url = "https://daa-documentos.unamad.edu.pe:8081/api/data/teacher/{$dni}";

        try {
            $response = Http::timeout(10)
                ->withOptions(['verify' => false])  // Agregar esta línea
                ->withToken($token)
                ->acceptJson()
                ->get($url);

            if (!$response->successful()) {
                return response()->json([
                    'message' => 'Error al consultar el docente.',
                    'codigo_http' => $response->status()
                ], 500);
            }

            $datos = $response->json();

            if (empty($datos) || !isset($datos["name"])) {
                return response()->json([
                    'message' => 'No se encontraron datos del docente.',
                    'datos' => $datos,
                ], 404);
            }

            $respuesta = [
                "codigo" => $datos["CODIGO"] ?? '',
                "nombre" => $datos["name"],
                "apaterno" => $datos["paternalSurname"] ?? '',
                "amaterno" => $datos["maternalSurname"] ?? '',
                "correo" => $datos["email"] ?? '',
                "correo_institucional" => ''
            ];

            return response()->json([
                'message' => 'Datos encontrados',
                'respuesta' => $respuesta,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error de conexión: ' . $e->getMessage()
            ], 500);
        }
    }
    public function consultarExterno($dni)
    {
        $url = "https://apidatos.unamad.edu.pe/api/consulta/{$dni}";

        try {
            $response = Http::timeout(10)
                ->withOptions(['verify' => false])  // Desactivar verificación SSL
                ->get($url);

            if ($response->successful()) {
                $datos = $response->json();

                if (empty($datos) || !isset($datos["NOMBRES"])) {
                    return response()->json(['message' => 'No se encontraron datos del externo.'], 404);
                }

                $respuesta = [
                    "codigo" => "",
                    "nombre" => $datos["NOMBRES"],
                    "apaterno" => $datos["AP_PAT"],
                    "amaterno" => $datos["AP_MAT"],
                    "correo" => "",
                    "correo_institucional" => ""
                ];
                return response()->json(['message' => 'Datos encontrados', 'respuesta' => $respuesta], 200);
            } else {
                return response()->json([
                    'message' => 'No se pudo consultar el externo.',
                    'codigo_http' => $response->status()
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error de conexión: ' . $e->getMessage()
            ], 500);
        }
    }
    public function consultarEstudiante($dni)
    {
        $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJqdGkiOiI3MjE4NzM0OC02OWQ1LTRjOGEtOTA2MC0zNzJiOTc3NzZiOTEiLCJodHRwOi8vc2NoZW1hcy54bWxzb2FwLm9yZy93cy8yMDA1LzA1L2lkZW50aXR5L2NsYWltcy9uYW1lIjoiZWhvbGdhZG8iLCJuYW1lIjoiZWhvbGdhZG8iLCJodHRwOi8vc2NoZW1hcy5taWNyb3NvZnQuY29tL3dzLzIwMDgvMDYvaWRlbnRpdHkvY2xhaW1zL3JvbGUiOiJBcGlDb25zdW1lciIsImV4cCI6MTc1Mjc4MjY5MSwiaXNzIjoiYzk4NGRmYjFhMDE3YTNlZjhiOTdlMjUzOWY3ZWNhYWEifQ.LI1iiBp3_aO25ab6qIGqeki-knEz-WgOfuiN8j8P4vY';
        $url = "https://daa-documentos.unamad.edu.pe:8081/api/data/student/{$dni}";

        try {
            $response = Http::timeout(30)
                ->withOptions(['verify' => false])  // Agregar esta línea
                ->withToken($token)
                ->acceptJson()
                ->get($url);

            if (!$response->successful()) {
                return response()->json([
                    'message' => 'Error al consultar el estudiante.',
                    'codigo_http' => $response->status()
                ], 500);
            }

            $datos = $response->json();

            if (empty($datos) || !isset($datos["status"]) || $datos["status"] !== "success") {
                return response()->json([
                    'message' => 'No se encontraron datos del estudiante.',
                    'datos' => $datos,
                ], 404);
            }

            if (empty($datos["data"]) || !isset($datos["data"][0]["info"])) {
                return response()->json([
                    'message' => 'No se encontraron datos del estudiante.',
                    'datos' => $datos,
                ], 404);
            }

            $info = $datos["data"][0]["info"];

            $respuesta = [
                "codigo" => $info["username"] ?? '',
                "nombre" => $info["name"] ?? '',
                "apaterno" => $info["paternalSurname"] ?? '',
                "amaterno" => $info["maternalSurname"] ?? '',
                "correo" => $info["email"] ?? '',
                "correo_institucional" => $info["email"] ?? ''
            ];

            return response()->json([
                'message' => 'Datos encontrados',
                'respuesta' => $respuesta,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error de conexión: ' . $e->getMessage()
            ], 500);
        }
    }
}
