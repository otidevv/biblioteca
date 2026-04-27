<?php

use App\Models\Carrera;
use App\Services\LectorImportService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    DB::table('roles')->insert([
        'id' => 5,
        'nombre' => 'Lector',
        'descripcion' => 'Rol de prueba',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
});

it('uses the configured default password when the import password cell is empty', function () {
    config()->set('auth.import_default_password', 'Temporal123');

    Carrera::create([
        'nombre' => 'Ingenieria de Sistemas e Informatica',
        'codigo' => 'ISI',
        'facultad' => 'Ingenieria',
    ]);

    $csv = <<<'CSV'
tipo_persona,dni,nombres,apellido_paterno,apellido_materno,sexo,telefono,email_personal,direccion,codigo_institucional,carrera,estado_academico,password
ESTUDIANTE,70000001,Ana Maria,Quispe,Huaman,F,987654321,ana.quispe@correo.com,AV. LOS LIBROS 123,202410001,Ingenieria de Sistemas e Informatica,1,
CSV;

    $file = UploadedFile::fake()->createWithContent('lectores.csv', $csv);

    $preview = app(LectorImportService::class)->preview($file);

    expect($preview['can_import'])->toBeTrue();
    expect($preview['summary']['validos'])->toBe(1);
    expect($preview['rows'][0]['data']['password'])->toBe('');
    expect($preview['rows'][0]['data']['password_resolved'])->toBe('Temporal123');
});

it('keeps showing every parsed row in preview instead of truncating the sheet', function () {
    $csv = <<<'CSV'
tipo_persona,dni,nombres,apellido_paterno,apellido_materno,sexo,telefono,email_personal,direccion,codigo_institucional,carrera,estado_academico,password
DOCENTE,70000011,Ana,Lopez,,F,900000001,ana1@correo.com,DIRECCION 1,,,,Clave123
DOCENTE,70000012,Beto,Ramos,,M,900000002,beto2@correo.com,DIRECCION 2,,,,Clave123
DOCENTE,70000013,Carla,Diaz,,F,900000003,carla3@correo.com,DIRECCION 3,,,,Clave123
CSV;

    $file = UploadedFile::fake()->createWithContent('lectores.csv', $csv);

    $preview = app(LectorImportService::class)->preview($file);

    expect($preview['summary']['total'])->toBe(3);
    expect($preview['rows'])->toHaveCount(3);
});

it('ignores sex and academic status and leaves unknown careers as null', function () {
    $csv = <<<'CSV'
tipo_persona,dni,nombres,apellido_paterno,apellido_materno,sexo,telefono,email_personal,direccion,codigo_institucional,carrera,estado_academico,password
ESTUDIANTE,70000021,Luis,Quispe,Perez,X,900000021,luis@correo.com,AV. SIEMPRE VIVA,202410021,CARRERA INEXISTENTE,EGRESADO,Clave123
CSV;

    $file = UploadedFile::fake()->createWithContent('lectores.csv', $csv);

    $preview = app(LectorImportService::class)->preview($file);
    $row = $preview['rows'][0];

    expect($preview['can_import'])->toBeTrue();
    expect($row['is_valid'])->toBeTrue();
    expect($row['data']['sexo'])->toBeNull();
    expect($row['data']['estado_academico'])->toBeNull();
    expect($row['data']['carrera_id'])->toBeNull();
    expect($row['data']['carrera'])->toBe('CARRERA INEXISTENTE');
});

it('imports only rows with a valid email and preserves the excel password when present', function () {
    config()->set('auth.import_default_password', 'Temporal123');

    $csv = <<<'CSV'
tipo_persona,dni,nombres,apellido_paterno,apellido_materno,sexo,telefono,email_personal,direccion,codigo_institucional,carrera,estado_academico,password
DOCENTE,70000031,Maria,Rojas,,F,900000031,maria@correo.com,DIRECCION 1,,,,ClaveExcel9
DOCENTE,70000032,Jose,Castro,,M,900000032,,DIRECCION 2,,,,
CSV;

    $file = UploadedFile::fake()->createWithContent('lectores.csv', $csv);
    $service = app(LectorImportService::class);

    $preview = $service->preview($file);
    $result = $service->import($preview['token'], $preview['rows']);

    expect($preview['summary']['total'])->toBe(2);
    expect($preview['summary']['validos'])->toBe(1);
    expect($preview['summary']['invalidos'])->toBe(1);
    expect($preview['can_import'])->toBeTrue();
    expect($result['created'])->toBe(1);

    $persona = DB::table('personas')->where('dni', '70000031')->first();
    $user = DB::table('users')->where('email', 'maria@correo.com')->first();

    expect($persona)->not->toBeNull();
    expect($persona->sexo)->toBeNull();
    expect($persona->estado_academico)->toBeNull();
    expect(DB::table('personas')->where('dni', '70000032')->exists())->toBeFalse();
    expect($user)->not->toBeNull();
    expect(Hash::check('ClaveExcel9', $user->password))->toBeTrue();
});
