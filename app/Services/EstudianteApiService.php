<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Consulta de estudiantes en la API institucional (daa-documentos).
 *
 * Devuelve siempre un arreglo con:
 *  - ok       bool
 *  - status   codigo HTTP sugerido para responder al cliente
 *  - message  mensaje para el usuario
 *  - info     datos normalizados del estudiante (solo si ok)
 */
class EstudianteApiService
{
    public function buscarPorDni(string $dni): array
    {
        $token = config('services.unamad_integrations.student_token');
        $verifySsl = config('services.unamad_integrations.verify_ssl', true);
        $baseUrl = rtrim((string) config('services.unamad_integrations.student_url', 'https://daa-documentos.unamad.edu.pe:8081/api/data/student'), '/');

        if (! $token) {
            return $this->error(503, 'La integracion institucional de estudiantes no esta configurada.');
        }

        try {
            $response = Http::timeout(30)
                ->withOptions(['verify' => $verifySsl])
                ->withToken($token)
                ->acceptJson()
                ->get($baseUrl . '/' . rawurlencode($dni));
        } catch (\Throwable $e) {
            Log::warning('No se pudo conectar con la API de estudiantes.', ['error' => $e->getMessage()]);

            return $this->error(500, 'Error de conexion con el servicio externo.');
        }

        if ($response->status() === 404) {
            return $this->error(404, 'No se encontraron datos del estudiante.');
        }

        if (! $response->successful()) {
            return $this->error(500, 'Error al consultar el estudiante.') + ['codigo_http' => $response->status()];
        }

        $datos = $response->json();
        $info = $datos['data'][0]['info'] ?? null;

        if (($datos['status'] ?? null) !== 'success' || ! is_array($info)) {
            return $this->error(404, 'No se encontraron datos del estudiante.');
        }

        return [
            'ok' => true,
            'status' => 200,
            'message' => 'Datos encontrados',
            'info' => [
                'dni' => trim((string) ($info['dni'] ?? $dni)),
                'codigo' => trim((string) ($info['username'] ?? '')),
                'nombres' => trim((string) ($info['name'] ?? '')),
                'apellido_paterno' => trim((string) ($info['paternalSurname'] ?? '')),
                'apellido_materno' => trim((string) ($info['maternalSurname'] ?? '')),
                'email' => trim((string) ($info['email'] ?? '')),
                'email_personal' => trim((string) ($info['personalEmail'] ?? '')),
                'carrera' => trim((string) ($info['carrerName'] ?? '')),
                'facultad' => trim((string) ($info['facultyName'] ?? '')),
            ],
        ];
    }

    private function error(int $status, string $message): array
    {
        return ['ok' => false, 'status' => $status, 'message' => $message, 'info' => null];
    }
}
