<?php

use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\AdministracionController;
use App\Http\Controllers\LectoresController;


//controllers de JS en Api
use App\Http\Controllers\Api\UsuarioController as ApiUsuarioController;
use App\Http\Controllers\Api\RolController as ApiRolController;
use App\Http\Controllers\Api\BibliotecaController as ApiBibliotecaController;
/*
|--------------------------------------------------------------------------
| Rutas públicas
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Rutas protegidas
|--------------------------------------------------------------------------
| auth           → usuario autenticado
| permiso.ruta   → validación automática por nombre de ruta
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';
Route::middleware(['auth', 'permiso.ruta'])->group(function () {

    Route::get('/', function () {
        return view('administracion/index');
    });    
    // ADMINISTRACIÓN
    Route::prefix('administracion')->group(function () {
        Route::get('{modulo}', [AdministracionController::class, 'index'])
            ->where('modulo', 'usuarios|roles_permisos|backups|bibliotecas');
    });

    // LECTORES
    Route::prefix('lectores')->group(function () {
        Route::get('{modulo}', [LectoresController::class, 'index'])
            ->where('modulo', 'registro|historial|penalizaciones|importacion');
    });

    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');



    //====================================METODOS DE JS=============================
    
    Route::prefix('api')->group(function () {
        Route::prefix('/usuarios')->group(function () {
            Route::get('/listar', [ApiUsuarioController::class, 'listar'])->name('usuarios.listar');
            Route::post('/nuevo', [ApiUsuarioController::class, 'nuevo'])->name('usuarios.nuevo');
            Route::post('/edit', [ApiUsuarioController::class, 'edit'])->name('usuarios.edit');
            Route::put('/update', [ApiUsuarioController::class, 'update'])->name('usuarios.update');
            Route::delete('/destroy', [ApiUsuarioController::class, 'destroy'])->name('usuarios.destroy');
        });
        Route::prefix('roles')->group(function () {
            Route::get('/listar', [ApiRolController::class, 'listar'])->name('roles.listar');
            Route::post('/nuevo', [ApiRolController::class, 'nuevo'])->name('roles.nuevo');
            Route::post('/edit', [ApiRolController::class, 'edit'])->name('roles.edit');
            Route::put('/update', [ApiRolController::class, 'update'])->name('roles.update');
            Route::post('/permisos/guardar', [ApiRolController::class, 'guardarPermisos'])->name('roles.permisos.guardar');
            Route::delete('/destroy', [ApiRolController::class, 'destroy'])->name('roles.destroy');
        });
        Route::prefix('bibliotecas')->group(function () {
            Route::get('/listar', [ApiBibliotecaController::class, 'listar']);
            Route::post('/nuevo', [ApiBibliotecaController::class, 'nuevo']);
            Route::post('/edit', [ApiBibliotecaController::class, 'edit']);
            Route::put('/{id}', [ApiBibliotecaController::class, 'update']);
            Route::delete('/{id}', [ApiBibliotecaController::class, 'destroy']);
        });
    });
});

