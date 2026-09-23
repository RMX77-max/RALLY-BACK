<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    CompetidorController,
    EventoController,
    TiempoController,
    CronometroController,
    GaleriaController,
    VideoController,
    CompetidorEventoController,
    AuthController,
    PatrocinadorController,
    TiemposArchivoController
};
use App\Http\Controllers\ContenidoRallyController;
use App\Http\Controllers\AdminContenidoRallyController;
use App\Http\Controllers\ImportacionRallyController;
use App\Http\Controllers\PilotoRallyController;
use App\Http\Controllers\MultimediaRallyController;
use App\Http\Controllers\ArchivoRallyController;

/*
|--------------------------------------------------------------------------
| Rutas públicas
|--------------------------------------------------------------------------
*/

// Health check
Route::get('/health', fn() => response()->json(['status' => 'ok', 'time' => now()]));

// === AUTENTICACIÓN (públicas) ===
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// === PÚBLICAS: vistas, datos, galería ===
Route::get('/patrocinadores', [PatrocinadorController::class, 'index']);
Route::get('/galeria', [GaleriaController::class, 'index']);
Route::get('/videos', [VideoController::class, 'index']);
Route::get('/competidores-evento', [CompetidorEventoController::class, 'index']);
Route::get('/rally/evento-activo', [ContenidoRallyController::class, 'eventoActivo']);
Route::get('/rally/resultados', [ContenidoRallyController::class, 'resultados']);
Route::get('/rally/pilotos', [PilotoRallyController::class, 'index']);
Route::get('/rally/galeria', [MultimediaRallyController::class, 'galeria']);
Route::get('/rally/videos', [MultimediaRallyController::class, 'videos']);
Route::get('/rally/patrocinadores', [MultimediaRallyController::class, 'patrocinadores']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/admin/archivos', [ArchivoRallyController::class, 'store']);
    Route::post('/admin/galeria', [MultimediaRallyController::class, 'guardarFoto']);
    Route::delete('/admin/galeria/{galeria}', [MultimediaRallyController::class, 'eliminarFoto']);
    Route::post('/admin/videos', [MultimediaRallyController::class, 'guardarVideo']);
    Route::delete('/admin/videos/{video}', [MultimediaRallyController::class, 'eliminarVideo']);
    Route::post('/admin/pilotos', [PilotoRallyController::class, 'store']);
    Route::match(['put', 'post'], '/admin/pilotos/{competidor}', [PilotoRallyController::class, 'update']);
    Route::delete('/admin/pilotos/{competidor}', [PilotoRallyController::class, 'destroy']);
    Route::put('/admin/eventos/{evento}', [AdminContenidoRallyController::class, 'guardarEvento']);
    Route::get('/admin/eventos/{evento}/categorias', [AdminContenidoRallyController::class, 'categorias']);
    Route::post('/admin/eventos/{evento}/categorias', [AdminContenidoRallyController::class, 'guardarCategoria']);
    Route::put('/admin/eventos/{evento}/categorias/{categoria}', [AdminContenidoRallyController::class, 'guardarCategoria']);
    Route::delete('/admin/eventos/{evento}/categorias/{categoria}', [AdminContenidoRallyController::class, 'eliminarCategoria']);
    Route::get('/admin/eventos/{evento}/equipos', [AdminContenidoRallyController::class, 'equipos']);
    Route::post('/admin/eventos/{evento}/equipos', [AdminContenidoRallyController::class, 'guardarEquipo']);
    Route::put('/admin/eventos/{evento}/equipos/{equipo}', [AdminContenidoRallyController::class, 'guardarEquipo']);
    Route::get('/admin/eventos/{evento}/etapas', [AdminContenidoRallyController::class, 'etapas']);
    Route::post('/admin/eventos/{evento}/etapas', [AdminContenidoRallyController::class, 'guardarEtapa']);
    Route::put('/admin/eventos/{evento}/etapas/{etapa}', [AdminContenidoRallyController::class, 'guardarEtapa']);
    Route::delete('/admin/eventos/{evento}/etapas/{etapa}', [AdminContenidoRallyController::class, 'eliminarEtapa']);
    Route::post('/admin/eventos/{evento}/cronograma', [AdminContenidoRallyController::class, 'guardarCronograma']);
    Route::put('/admin/eventos/{evento}/cronograma/{cronograma}', [AdminContenidoRallyController::class, 'guardarCronograma']);
    Route::post('/admin/eventos/{evento}/banners', [AdminContenidoRallyController::class, 'guardarBanner']);
    Route::put('/admin/eventos/{evento}/banners/{banner}', [AdminContenidoRallyController::class, 'guardarBanner']);
    Route::put('/admin/eventos/{evento}/recorrido', [AdminContenidoRallyController::class, 'guardarRecorrido']);
    Route::put('/admin/eventos/{evento}/resultados/publicacion', [AdminContenidoRallyController::class, 'publicarResultados']);
    Route::post('/admin/importaciones/previsualizar', [ImportacionRallyController::class, 'previsualizar']);
    Route::post('/admin/importaciones/{importacion}/confirmar', [ImportacionRallyController::class, 'confirmar']);
    Route::post('/galeria/foto', [GaleriaController::class, 'store']);
    Route::delete('/galeria/foto/{id}', [GaleriaController::class, 'destroy']);
    Route::post('/patrocinadores', [PatrocinadorController::class, 'store']);
    Route::post('/patrocinadores/{id}', [PatrocinadorController::class, 'update']);
    Route::delete('/patrocinadores/{id}', [PatrocinadorController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Rutas protegidas (solo admin con token)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // === Cerrar sesión ===
    Route::post('/logout', [AuthController::class, 'logout']);

    // === Competidores evento ===

    Route::post('/competidores-evento', [CompetidorEventoController::class, 'store']);
    Route::put('/competidores-evento/{id}', [CompetidorEventoController::class, 'update']);
    Route::post('/competidores-evento/importar', [CompetidorEventoController::class, 'importarExcel']);
    Route::post('/competidores-evento/{id}/foto', [CompetidorEventoController::class, 'subirFoto']);
    Route::delete('/competidores-evento/{id}', [CompetidorEventoController::class, 'destroy']);


    // === Galería y videos ===
    Route::post('/galeria', [GaleriaController::class, 'store']);
    Route::delete('/galeria/{id}', [GaleriaController::class, 'destroy']);

    Route::post('/videos', [VideoController::class, 'store']);
    Route::delete('/videos/{id}', [VideoController::class, 'destroy']);

    // === tiempos archivo excel ===


    Route::post  ('/tiempos/archivos',       [TiemposArchivoController::class, 'store']);   // subir (admin)

    Route::delete('/tiempos/archivos/{id}',  [TiemposArchivoController::class, 'destroy']); // borrar (admin)
});

Route::get   ('/tiempos/archivos',[TiemposArchivoController::class, 'index']);   // listar
Route::get   ('/tiempos/archivos/{id}',[TiemposArchivoController::class, 'show']);    // ver uno
/*
|--------------------------------------------------------------------------
| Rutas de cronometraje (pueden mantenerse públicas)
|--------------------------------------------------------------------------
*/
Route::controller(CompetidorController::class)->group(function () {
    Route::get('/competidores', 'index');
    Route::post('/competidores', 'store');
    Route::put('/competidores/{id}', 'update');
    Route::delete('/competidores/{id}', 'destroy');
});

Route::prefix('tiempos')->controller(TiempoController::class)->group(function () {
    Route::post('/batch', 'storeBatch');
    Route::get('/etapa/{etapa}', 'porEtapa');
    Route::get('/general', 'clasificacionGeneral');
});

Route::controller(EventoController::class)->group(function () {
    Route::get('/eventos', 'index');
    Route::post('/eventos', 'store');
    Route::get('/eventos/{id}', 'show');
    Route::put('/eventos/{id}', 'update');
    Route::delete('/eventos/{id}', 'destroy');
});

Route::prefix('cronometro')->controller(CronometroController::class)->group(function () {
    Route::post('/iniciar', 'iniciar');
    Route::get('/tiempo-actual', 'tiempoActual');
    Route::post('/detener', 'detener');
});
