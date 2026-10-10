<?php

namespace App\Services;

use App\Models\Carrera;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Prestamo libre: el estudiante se identifica solo con su DNI.
 *
 * Si ya existe en el sistema se usa su usuario. Si no, se consulta la API institucional
 * (o, si no figura ahi, los datos que ingresa el bibliotecario) y se registra como usuario
 * "libre": sin rol de lector y sin acceso al sistema (estado 0), de modo que solo puede
 * recibir prestamos en sala por esta via.
 */
class LectorLibreService
{
    // Marca de los usuarios creados por prestamo libre (users.tipo_usuario).
    const TIPO_USUARIO = 'Libre';

    public function __construct(private EstudianteApiService $api)
    {
    }

    /**
     * Datos del estudiante para mostrar y registrar el prestamo.
     * No escribe en la base: la API se consulta aqui, fuera de cualquier transaccion.
     */
    public function consultar(string $dni): array
    {
        $persona = Persona::with(['user.roles', 'carrera'])->where('dni', $dni)->first();

        if ($persona && $persona->user) {
            return [
                'ok' => true,
                'status' => 200,
                'registrado' => true,
                'es_lector' => $persona->user->roles->contains('id', 5),
                'info' => [
                    'dni' => $persona->dni,
                    'codigo' => (string) $persona->codigo_institucional,
                    'nombres' => $persona->nombres,
                    'apellido_paterno' => $persona->apellido_paterno,
                    'apellido_materno' => (string) $persona->apellido_materno,
                    'email' => $persona->user->email,
                    'email_personal' => (string) $persona->email_personal,
                    'carrera' => (string) $persona->carrera?->nombre,
                    'carrera_id' => $persona->carrera_id,
                    'facultad' => '',
                ],
                'carnet' => $persona->tipo_carnet,
                'carnet_fecha' => $persona->fecha_carnet_duplicado?->format('d/m/Y'),
            ];
        }

        $cacheKey = 'prestamo_libre:estudiante:' . $dni;
        $info = Cache::get($cacheKey);

        if (! $info) {
            $resultado = $this->api->buscarPorDni($dni);

            if (! $resultado['ok']) {
                return $resultado;
            }

            $info = $resultado['info'];
            Cache::put($cacheKey, $info, now()->addMinutes(30));
        }

        $info['carrera_id'] = $info['carrera'] !== '' ? Carrera::where('nombre', $info['carrera'])->value('id') : null;

        return ['ok' => true, 'status' => 200, 'registrado' => false, 'es_lector' => false, 'info' => $info];
    }

    /**
     * Datos ingresados a mano por el bibliotecario cuando el DNI no figura en el sistema academico.
     */
    public function infoManual(string $dni, string $nombreCompleto, int $carreraId): array
    {
        [$nombres, $paterno, $materno] = $this->separarNombre($nombreCompleto);

        return [
            'dni' => $dni,
            'codigo' => '',
            'nombres' => $nombres,
            'apellido_paterno' => $paterno,
            'apellido_materno' => $materno,
            'email' => '',
            'email_personal' => '',
            'carrera' => '',
            'carrera_id' => $carreraId,
            'facultad' => '',
        ];
    }

    /**
     * Usuario al que se asigna el prestamo. Llamar dentro de la transaccion del prestamo.
     */
    public function usuarioParaPrestamo(string $dni, array $info): User
    {
        $persona = Persona::createOrFirst(['dni' => $dni], [
            'tipo_persona' => 'ESTUDIANTE',
            'nombres' => $info['nombres'] ?: 'SIN NOMBRE',
            'apellido_paterno' => $info['apellido_paterno'] ?: '-',
            'apellido_materno' => $info['apellido_materno'] ?: null,
            'email_personal' => $info['email_personal'] ? Str::lower($info['email_personal']) : null,
            'codigo_institucional' => $this->codigoDisponible($info['codigo']),
            'carrera_id' => $info['carrera_id'] ?? null,
        ]);

        if ($persona->user) {
            return $persona->user;
        }

        return User::create([
            'name' => $this->nombreDisponible($persona, $dni),
            'email' => $this->correoDisponible($info, $dni),
            'password' => Hash::make(Str::random(40)),
            'estado' => 0,
            'origen' => 'local',
            'tipo_usuario' => self::TIPO_USUARIO,
            'persona_id' => $persona->id,
        ]);
    }

    /**
     * Acepta "Apellidos, Nombres" (como lo muestra el sistema) o "Nombres Apellido_paterno Apellido_materno".
     */
    private function separarNombre(string $nombreCompleto): array
    {
        $texto = Str::upper(Str::squish($nombreCompleto));

        if (str_contains($texto, ',')) {
            [$apellidos, $nombres] = array_map('trim', explode(',', $texto, 2));
            $apellidos = explode(' ', $apellidos, 2);

            return [$nombres, $apellidos[0], $apellidos[1] ?? ''];
        }

        $partes = explode(' ', $texto);

        if (count($partes) < 3) {
            return [$partes[0], $partes[1] ?? '', ''];
        }

        $materno = array_pop($partes);
        $paterno = array_pop($partes);

        return [implode(' ', $partes), $paterno, $materno];
    }

    private function codigoDisponible(string $codigo): ?string
    {
        return $codigo !== '' && ! Persona::where('codigo_institucional', $codigo)->exists() ? $codigo : null;
    }

    private function nombreDisponible(Persona $persona, string $dni): string
    {
        $nombre = trim(implode(' ', array_filter([$persona->nombres, $persona->apellido_paterno, $persona->apellido_materno])));

        return User::where('name', $nombre)->exists() ? $nombre . ' (DNI ' . $dni . ')' : $nombre;
    }

    private function correoDisponible(array $info, string $dni): string
    {
        foreach ([$info['email'], $info['email_personal']] as $correo) {
            $correo = Str::lower(trim((string) $correo));

            if ($correo !== '' && ! User::where('email', $correo)->exists()) {
                return $correo;
            }
        }

        return $dni . '@prestamo-libre.local';
    }
}
