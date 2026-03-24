<?php

use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\AdministracionController;
use App\Http\Controllers\LectoresController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\PaginaController;
use App\Http\Controllers\PrestamoController;
use App\Http\Controllers\SincronizarController;
use App\Http\Controllers\Auth\ProfileController;

//controllers de JS en Api
use App\Http\Controllers\Api\UsuarioController as ApiUsuarioController;
use App\Http\Controllers\Api\RolController as ApiRolController;
use App\Http\Controllers\Api\BibliotecaController as ApiBibliotecaController;
use App\Http\Controllers\Api\LectorController as ApiLectorController;
use App\Http\Controllers\Api\ConsultaApiController as ApiConsultaApiController;
use App\Http\Controllers\Api\ProveedorController as ApiProveedorController;
use App\Http\Controllers\Api\EditorialController as ApiEditorialController;
use App\Http\Controllers\Api\Tipo_registroController as ApiTipoRegistroController;
use App\Http\Controllers\Api\AutorController as ApiAutorController;
use App\Http\Controllers\Api\InventarioController as ApiInventarioController;
use App\Http\Controllers\Api\LibroController as ApiLibroController;
use App\Http\Controllers\Api\MateriaController as ApiMateriaController;
use App\Http\Controllers\Api\DeweyController as ApiDeweyController;
use App\Http\Controllers\Api\CutterController as ApiCutterController;
use App\Http\Controllers\Api\EjemplarController as ApiEjemplarController;
use App\Http\Controllers\Api\CompraController as ApiCompraController;
use App\Http\Controllers\Api\PaginaController as ApiPaginaController;
use App\Http\Controllers\Api\ReservacionController as ApiReservacionController;

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

    Route::get('/', [AdministracionController::class, 'inicio'])->name('administracion.index');    
    Route::get('/perfil', [ProfileController::class, 'edit'])->name('perfil.edit');    

    // ADMINISTRACIÓN
    Route::prefix('administracion')->group(function () {
        Route::get('{modulo}/{id?}', [AdministracionController::class, 'index'])
            ->where('modulo', 'usuarios|roles_permisos|backups|bibliotecas|proveedores|editoriales|tipo_registros|autores|compras|libros|libros_nuevo|ejemplares');
    }); 

    // INVENTARIO
    Route::prefix('inventario')->group(function () {
        Route::get('{modulo}', [InventarioController::class, 'index'])
            ->where('modulo', 'catalogo|compras|reportes|compra_nuevo');
    });

    // PRESTAMO
    Route::prefix('prestamos')->group(function () {
        Route::get('{modulo}', [PrestamoController::class, 'index'])
            ->where('modulo', 'reservas|prestamos|reportes|compra_nuevo');
    });

    // LECTORES
    Route::prefix('lectores')->group(function () {
        Route::get('{modulo}', [LectoresController::class, 'index'])
            ->where('modulo', 'registro|historial|penalizaciones|importacion');
    });

    Route::get('/dashboard', function () {
        return view('administracion.index'); // resources/views/administracion/index.blade.php
    })->name('dashboard');




    //====================================METODOS DE JS=============================
    
    Route::prefix('api')->group(function () {
        Route::prefix('/usuarios')->group(function () {
            //metodos de modulos usuarios de administracion
            Route::get('/listar', [ApiUsuarioController::class, 'listar'])->name('usuarios.listar');
            Route::post('/nuevo', [ApiUsuarioController::class, 'nuevo'])->name('usuarios.nuevo');
            Route::post('/edit', [ApiUsuarioController::class, 'edit'])->name('usuarios.edit');
            Route::delete('/contrasena', [ApiUsuarioController::class, 'cambiarContrasena'])->name('usuarios.cambiar.contrasena');
            //metodos del modulo lectores de Lectores
            Route::get('/lectores/listar', [ApiUsuarioController::class, 'listarLectores'])->name('lectores.listar');
            Route::post('/lectores/nuevo', [ApiUsuarioController::class, 'nuevoLector'])->name('lectores.nuevo');
            Route::post('/lectores/edit', [ApiUsuarioController::class, 'editLector'])->name('lectores.edit');
            //busqueda de dni en api externa

        });
        Route::prefix('roles')->group(function () {
            Route::get('/listar', [ApiRolController::class, 'listar'])->name('roles.listar');
            Route::post('/nuevo', [ApiRolController::class, 'nuevo'])->name('roles.nuevo');
            Route::post('/edit', [ApiRolController::class, 'edit'])->name('roles.edit');
            Route::post('/permisos/guardar', [ApiRolController::class, 'guardarPermisos'])->name('roles.permisos.guardar');
        });
        Route::prefix('bibliotecas')->group(function () {
            Route::get('/listar', [ApiBibliotecaController::class, 'listar']);
            Route::post('/nuevo', [ApiBibliotecaController::class, 'nuevo']);
            Route::post('/edit', [ApiBibliotecaController::class, 'edit']);
        });
        Route::prefix('proveedores')->group(function () {
            Route::get('/listar', [ApiProveedorController::class, 'listar']);
            Route::post('/nuevo', [ApiProveedorController::class, 'nuevo']);
            Route::post('/edit', [ApiProveedorController::class, 'edit']);
        });
        Route::prefix('editoriales')->group(function () {
            Route::get('/listar', [ApiEditorialController::class, 'listar']);
            Route::post('/nuevo', [ApiEditorialController::class, 'nuevo']);
            Route::post('/edit', [ApiEditorialController::class, 'edit']);
        });
        Route::prefix('lectores')->group(function () {
            Route::get('/listar', [ApiLectorController::class, 'listar']);
            Route::post('/nuevo', [ApiLectorController::class, 'nuevo']);
            Route::post('/edit', [ApiLectorController::class, 'edit']);
            Route::put('/{id}', [ApiLectorController::class, 'update']);
            Route::delete('/{id}', [ApiLectorController::class, 'destroy']);
        });
        Route::prefix('tipo_registros')->group(function () {
            Route::get('/listar', [ApiTipoRegistroController::class, 'listar']);
            Route::post('/nuevo', [ApiTipoRegistroController::class, 'nuevo']);
            Route::post('/edit', [ApiTipoRegistroController::class, 'edit']);
            Route::delete('/{id}', [ApiTipoRegistroController::class, 'destroy']);
        });
        Route::prefix('autores')->group(function () {
            Route::get('/listar', [ApiAutorController::class, 'listar']);
            Route::post('/nuevo', [ApiAutorController::class, 'nuevo']);
            Route::post('/edit', [ApiAutorController::class, 'edit']);
            Route::delete('/{id}', [ApiAutorController::class, 'destroy']);
        });
        //NUEVOS LIBROS EJEMPLARES
        Route::prefix('administracion')->group(function () {
            Route::get('/libros/ejemplar/listar', [ApiEjemplarController::class, 'listar']);
            Route::get('/autores', [ApiAutorController::class, 'listarAutores']);
            Route::get('/editoriales', [ApiEditorialController::class, 'listarEditoriales']);
            Route::get('/materias', [ApiMateriaController::class, 'listarMaterias']);
            Route::get('/libros', [ApiLibroController::class, 'buscar']);
            Route::get('/dewey/buscar', [ApiDeweyController::class, 'dewey_buscar']);
            Route::get('/codigo_cutter', [ApiCutterController::class, 'codigoCutter']);
            Route::get('/libros/check_codigo', [ApiCutterController::class, 'checkCodigo']);
            Route::post('/libros/guardar', [ApiLibroController::class, 'nuevo']);
            Route::get('/libros/listar', [ApiLibroController::class, 'listar']);
        });
        //CONSULTA DE COMPRAS EN INVENTARIO
        Route::prefix('inventario')->group(function () {
            Route::get('/compras/listar', [ApiCompraController::class, 'listarCompras']);
            Route::get('/autores', [ApiAutorController::class, 'listarAutores']);
            Route::get('/editoriales', [ApiEditorialController::class, 'listarEditoriales']);
            Route::get('/materias', [ApiMateriaController::class, 'listarMaterias']);
            Route::get('/libros', [ApiLibroController::class, 'buscar']);
            Route::get('/dewey/buscar', [ApiDeweyController::class, 'dewey_buscar']);
            Route::get('/codigo_cutter', [ApiCutterController::class, 'codigoCutter']);
            Route::get('/libros/check_codigo', [ApiCutterController::class, 'checkCodigo']);
            Route::post('/libros/guardar', [ApiLibroController::class, 'nuevo']);
            Route::get('/libros/listar', [ApiLibroController::class, 'listar']);
            Route::post('/ejemplares/guardar', [ApiEjemplarController::class, 'guardar']);
            Route::post('/ejemplares/actualizar', [ApiEjemplarController::class, 'actualizar']);
            Route::post('/ejemplares/enviar-biblioteca', [ApiEjemplarController::class, 'enviarBiblioteca']);
            Route::post('/compras/guardar', [ApiCompraController::class, 'guardarCompra']);
        });
        //NUEVOS LIBROS EJEMPLARES  RESERVADOS
        Route::prefix('prestamos')->group(function () {
            Route::get('reservas/listar', [ApiReservacionController::class, 'listar']);
            Route::post('reserva/{id}/entregar', [ApiReservacionController::class, 'entregar']);
            });
        //CONSULTA DE DNI EN API EXTERNA
        Route::prefix('externo')->group(function () {
            Route::get('/buscar-dni', [ApiConsultaApiController::class, 'consulta_api'])->name('usuarios.buscar.dni');            
        });

    });
});
// PAGINA Route::get('/autores', [ApiAutorController::class, 'listarAutores']);
Route::prefix('pagina')->group(function () {
    Route::post('/comentario', [ApiPaginaController::class, 'agregarComentario'])->name('comentario');
    Route::get('/idiomas', [ApiPaginaController::class, 'listarIdiomas']);
    Route::get('/materias', [ApiPaginaController::class, 'listarMaterias']); 
    Route::get('/autores', [ApiPaginaController::class, 'listarAutores']); 
    Route::get('/registros', [ApiPaginaController::class, 'listarRegistros']); 
    Route::get('/catalogo', [ApiPaginaController::class, 'catalogo'])->name('catalogo.libros'); 
    Route::get('{id}/ejemplares/biblioteca', [ApiPaginaController::class, 'ejemplarBiblioteca']); 
    Route::post('/reservar', [ApiReservacionController::class, 'nuevaReserva'])->name('reservar');
    Route::post('/reserva/{id}/cancelar', [ApiReservacionController::class, 'cancelarReserva'])->middleware('auth')->name('reserva.cancelar');
    Route::get('/libro/{id}/disponibilidad', [ApiPaginaController::class, 'disponibilidad']);
    Route::get('/libro/{id}/ejemplares', [ApiPaginaController::class, 'ejemplares']);
    }); 

Route::get('/', [PaginaController::class, 'index'])->name('home');
Route::get('/biblioteca/{id}', [PaginaController::class, 'showBiblioteca'])->name('biblioteca.show');
Route::get('/{id}/libro', [PaginaController::class, 'showLibro'])->name('libro.show');
Route::get('/catalogo', [PaginaController::class, 'catalogo'])->name('catalogo');
Route::get('/evento', [PaginaController::class, 'catalogo'])->name('evento');
Route::get('/prestamos', [PaginaController::class, 'nuevoPrestamo'])->name('prestamos');
Route::get('/reservar', [PaginaController::class, 'misReservas'])->name('mis.reservas');
/*
Route::get('/', [PaginaController::class, 'index'])->name('pagina.index');
Route::get('/libro', [PaginaController::class, '_libro']);
Route::get('/{id}/libro', [PaginaController::class, 'libro'])->name('pagina.libro');
*/

Route::get('/sincronizar', [SincronizarController::class, 'sincronizar']);
