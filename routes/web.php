<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

// Controllers
use App\Http\Controllers\AdministracionController;
use App\Http\Controllers\LectoresController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\PaginaController;
use App\Http\Controllers\PrestamoController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\SincronizarController;
use App\Http\Controllers\ProfileController as AuthProfileController;

//controllers de JS en Api
use App\Http\Controllers\Api\UsuarioController as ApiUsuarioController;
use App\Http\Controllers\Api\RolController as ApiRolController;
use App\Http\Controllers\Api\BibliotecaController as ApiBibliotecaController;
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
use App\Http\Controllers\Api\PrestamoController as ApiPrestamoController;
use App\Http\Controllers\Api\NotificacionController as ApiNotificacionController;
use App\Http\Controllers\Api\ActividadController as ApiActividadController;

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
Route::middleware('auth')
    ->get('/dashboard', [AdministracionController::class, 'inicio'])
    ->name('dashboard');

Route::middleware('auth')
    ->get('/manuales/codificacion', function () {
        $rutaManual = base_path('MANUAL_CODIFICACION.md');
        abort_unless(File::exists($rutaManual), 404);

        return view('administracion.manual_codificacion', [
            'contenido' => Str::markdown(File::get($rutaManual)),
        ]);
    })
    ->name('manual.codificacion');

Route::middleware('auth')
    ->get('/dashboard/aprendizaje-clasificacion', function () {
        return view('administracion.aprendizaje_clasificacion');
    })
    ->name('manual.aprendizaje.clasificacion');

Route::middleware('auth')
    ->get('/manuales/aprendizaje-clasificacion', function () {
        return redirect()->route('manual.aprendizaje.clasificacion');
    });

Route::middleware('auth')
    ->get('/perfil', [AuthProfileController::class, 'edit'])
    ->name('perfil.edit');

Route::middleware('auth')
    ->get('/reportes/descargas', [ReporteController::class, 'index'])
    ->name('reportes.descargas');

Route::middleware('auth')
    ->get('/reportes/descargas/{reporte}/archivo', [ReporteController::class, 'descargar'])
    ->name('reportes.descargar');
Route::middleware('auth')
    ->post('/reportes/descargas/{reporte}/reintentar', [ReporteController::class, 'reintentar'])
    ->name('reportes.reintentar');

Route::middleware(['auth', 'permiso.ruta'])->group(function () {
    Route::get('/reportes/grafico', [ReporteController::class, 'grafico'])
        ->name('reportes.grafico');

    Route::get('/inicio', [AdministracionController::class, 'inicio'])->name('administracion.index');
// ADMINISTRACIÓN
    Route::prefix('administracion')->group(function () {
        Route::get('{modulo}/{id?}', [AdministracionController::class, 'index'])
            ->where('modulo', 'usuarios|roles_permisos|backups|bibliotecas|proveedores|editoriales|tipo_registros|autores|compras|libros|libros_editar|libros_nuevo|ejemplares|sanciones|notificaciones|actividades');
    }); 

    // INVENTARIO
    Route::prefix('inventario')->group(function () {
        Route::post('fisico/reportes/solicitar', [InventarioController::class, 'solicitarReporteFisico']);
        Route::get('fisico/reportes/{reporte}/descargar', [InventarioController::class, 'descargarReporteFisico']);
        Route::get('{modulo}', [InventarioController::class, 'index'])
            ->where('modulo', 'catalogo|compras|reportes|compra_nuevo|fisico');
    });

    // PRESTAMO
    Route::prefix('prestamos')->group(function () {
        Route::get('{modulo}', [PrestamoController::class, 'index'])
            ->where('modulo', 'reservas|registro|historial|reportes|compra_nuevo');
    });

    // LECTORES
    Route::prefix('lectores')->group(function () {
        Route::get('importacion/plantilla', [LectoresController::class, 'descargarPlantillaImportacion'])
            ->name('lectores.importacion.plantilla');
        Route::post('historial/reportes/solicitar', [LectoresController::class, 'solicitarReporteHistorial']);
        Route::get('historial/reportes/{reporte}/descargar', [LectoresController::class, 'descargarReporteHistorial']);
        Route::get('{modulo}', [LectoresController::class, 'index'])
            ->where('modulo', 'registro|historial|penalizaciones|importacion');
    });

    //====================================METODOS DE JS=============================
    
    Route::prefix('api')->group(function () {
        Route::prefix('/usuarios')->group(function () {
            //metodos de modulos usuarios de administracion
            Route::get('/listar', [ApiUsuarioController::class, 'listar'])->name('usuarios.listar');
            Route::post('/nuevo', [ApiUsuarioController::class, 'nuevo'])->name('usuarios.nuevo');
            Route::post('/edit', [ApiUsuarioController::class, 'edit'])->name('usuarios.edit');
            Route::post('/contrasena', [ApiUsuarioController::class, 'cambiarContrasena'])->name('usuarios.cambiar.contrasena');
            //metodos del modulo lectores de Lectores
            Route::get('/lectores/listar', [ApiUsuarioController::class, 'listarLectores'])->name('lectores.listar');
            Route::post('/lectores/nuevo', [ApiUsuarioController::class, 'nuevoLector'])->name('lectores.nuevo');
            Route::post('/lectores/edit', [ApiUsuarioController::class, 'editLector'])->name('lectores.edit');
            Route::post('/lectores/importacion/preview', [ApiUsuarioController::class, 'previewImportacionLectores'])->name('lectores.importacion.preview');
            Route::post('/lectores/importacion/cargar', [ApiUsuarioController::class, 'cargarImportacionLectores'])->name('lectores.importacion.cargar');
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
        Route::prefix('tipo_registros')->group(function () {
            Route::get('/listar', [ApiTipoRegistroController::class, 'listar']);
            Route::post('/nuevo', [ApiTipoRegistroController::class, 'nuevo']);
            Route::post('/edit', [ApiTipoRegistroController::class, 'edit']);
            Route::delete('/{id}', [ApiTipoRegistroController::class, 'destroy']);
        });
        Route::prefix('sanciones')->group(function () {
            Route::get('/listar', [\App\Http\Controllers\Api\TipoSancionController::class, 'listar']);
            Route::post('/nuevo', [\App\Http\Controllers\Api\TipoSancionController::class, 'nuevo']);
            Route::post('/edit', [\App\Http\Controllers\Api\TipoSancionController::class, 'edit']);
            Route::get('/{tipoSancion}/reglas', [\App\Http\Controllers\Api\TipoSancionController::class, 'listarReglas']);
            Route::post('/reglas/guardar', [\App\Http\Controllers\Api\TipoSancionController::class, 'guardarRegla']);
        });
        Route::prefix('notificaciones')->group(function () {
            Route::get('/listar', [ApiNotificacionController::class, 'listar']);
            Route::get('/recursos', [ApiNotificacionController::class, 'recursosFormulario']);
            Route::post('/nuevo', [ApiNotificacionController::class, 'nuevo']);
            Route::post('/edit', [ApiNotificacionController::class, 'edit']);
        });
        Route::prefix('actividades')->group(function () {
            Route::get('/listar', [ApiActividadController::class, 'listar']);
            Route::post('/nuevo', [ApiActividadController::class, 'nuevo']);
            Route::post('/edit', [ApiActividadController::class, 'edit']);
            Route::get('/categorias/listar', [ApiActividadController::class, 'listarCategorias']);
            Route::post('/categorias/nuevo', [ApiActividadController::class, 'nuevaCategoria']);
            Route::post('/categorias/edit', [ApiActividadController::class, 'editarCategoria']);
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
            Route::get('/libros/sugerir-dewey', [ApiLibroController::class, 'sugerirDewey']);
            Route::get('/libros/generar-codigo', [ApiLibroController::class, 'generarCodigo']);
            Route::post('/libros/guardar', [ApiLibroController::class, 'nuevo']);
            Route::get('/libros/listar', [ApiLibroController::class, 'listar']);
        });
        //CONSULTA DE COMPRAS EN INVENTARIO
        Route::prefix('inventario')->group(function () {
            Route::get('/compras/listar', [ApiCompraController::class, 'listarCompras']);
            Route::get('/compras/{id}', [ApiCompraController::class, 'ver']);
            Route::get('/fisico/listar', [ApiInventarioController::class, 'listarInventarioFisico']);
            Route::get('/autores', [ApiAutorController::class, 'listarAutores']);
            Route::get('/editoriales', [ApiEditorialController::class, 'listarEditoriales']);
            Route::get('/materias', [ApiMateriaController::class, 'listarMaterias']);
            Route::get('/libros', [ApiLibroController::class, 'buscar']);
            Route::get('/dewey/buscar', [ApiDeweyController::class, 'dewey_buscar']);
            Route::get('/codigo_cutter', [ApiCutterController::class, 'codigoCutter']);
            Route::get('/libros/check_codigo', [ApiCutterController::class, 'checkCodigo']);
            Route::get('/libros/sugerir-dewey', [ApiLibroController::class, 'sugerirDewey']);
            Route::get('/libros/generar-codigo', [ApiLibroController::class, 'generarCodigo']);
            Route::post('/libros/guardar', [ApiLibroController::class, 'nuevo']);
            Route::post('/libros/actualizar', [ApiLibroController::class, 'actualizar']);
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
            Route::get('prestamos/listar', [ApiPrestamoController::class, 'listar']);
            Route::get('{id}/preview-sancion', [ApiPrestamoController::class, 'previewSancion']);
            Route::post('{id}/devolver', [ApiPrestamoController::class, 'devolver']);
            });
        //CONSULTA DE DNI EN API EXTERNA
        Route::prefix('externo')->group(function () {
            Route::get('/buscar-dni', [ApiConsultaApiController::class, 'consulta_api'])->name('usuarios.buscar.dni');            
        });

    });
});
// PAGINA Route::get('/autores', [ApiAutorController::class, 'listarAutores']);
Route::prefix('pagina')->group(function () {
    Route::post('/comentario', [ApiPaginaController::class, 'agregarComentario'])->middleware('auth')->name('comentario');
    Route::get('/idiomas', [ApiPaginaController::class, 'listarIdiomas']);
    Route::get('/materias', [ApiPaginaController::class, 'listarMaterias']); 
    Route::get('/autores', [ApiPaginaController::class, 'listarAutores']); 
    Route::get('/registros', [ApiPaginaController::class, 'listarRegistros']); 
    Route::get('/catalogo', [ApiPaginaController::class, 'catalogo'])->name('catalogo.libros'); 
    Route::get('{id}/ejemplares/biblioteca', [ApiPaginaController::class, 'ejemplarBiblioteca']); 
    Route::post('/reservar', [ApiReservacionController::class, 'nuevaReserva'])->middleware('auth')->name('reservar');
    Route::post('/reserva/{id}/cancelar', [ApiReservacionController::class, 'cancelarReserva'])->middleware('auth')->name('reserva.cancelar');
    Route::get('/libro/{id}/disponibilidad', [ApiPaginaController::class, 'disponibilidad']);
    Route::get('/libro/{id}/ejemplares', [ApiPaginaController::class, 'ejemplares']);
    Route::get('/libro/{id}/rating', [ApiPaginaController::class, 'rating']);
    }); 

Route::get('/', [PaginaController::class, 'index'])->name('home');
Route::get('/biblioteca/{id}', [PaginaController::class, 'showBiblioteca'])->name('biblioteca.show');
Route::get('/{id}/libro', [PaginaController::class, 'showLibro'])->name('libro.show');
Route::get('/catalogo', [PaginaController::class, 'catalogo'])->name('catalogo');
Route::get('/evento', [PaginaController::class, 'eventos'])->name('evento');
Route::middleware('auth')
    ->get('/prestamos', [PaginaController::class, 'misPrestamos'])
    ->name('prestamos');
Route::get('/reservar', [PaginaController::class, 'misReservas'])->middleware('auth')->name('mis.reservas');
/*
Route::get('/', [PaginaController::class, 'index'])->name('pagina.index');
Route::get('/libro', [PaginaController::class, '_libro']);
Route::get('/{id}/libro', [PaginaController::class, 'libro'])->name('pagina.libro');
*/

Route::middleware('auth')->group(function () {
    Route::get('/sincronizar', [SincronizarController::class, 'sincronizar']);
    Route::get('/clasificarLibrosMasivos', [SincronizarController::class, 'clasificarLibrosMasivos']);
    Route::get('/actualizarCodigosTopograficos', [SincronizarController::class, 'actualizarCodigosTopograficos']);
    
    Route::get('/sincronizarCirculacion', [SincronizarController::class, 'sincronizarCirculacion']);
    Route::get('/obtenerDeweyPorTitulo', [SincronizarController::class, 'obtenerDeweyPorTitulo']);
});




