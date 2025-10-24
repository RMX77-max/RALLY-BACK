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
Route::middleware('auth:sanctum')->group(function () {
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
