<?php

use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\AdministracionController;
use App\Http\Controllers\LectoresController;


//controllers de JS en Api
use App\Http\Controllers\Api\UsuarioController as ApiUsuarioController;

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
            Route::put('/edit', [ApiUsuarioController::class, 'edit'])->name('usuarios.edit');
            Route::put('/update', [ApiUsuarioController::class, 'update'])->name('usuarios.update');
            Route::delete('/destroy', [ApiUsuarioController::class, 'destroy'])->name('usuarios.destroy');
        });
        Route::prefix('roles')->group(function () {
            Route::get('/', [ApiRolController::class, 'index']);
            Route::post('/nuevo', [ApiRolController::class, 'nuevo']);
            Route::get('/{id}', [ApiRolController::class, 'show']);
            Route::put('/{id}', [ApiRolController::class, 'update']);
            Route::delete('/{id}', [ApiRolController::class, 'destroy']);
        });
    });
});

