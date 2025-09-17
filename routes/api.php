<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompetidorController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\TiempoController;
use App\Http\Controllers\CronometroController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Aquí definimos las rutas de la API para tu sistema de cronometraje.
|
*/

// Health check
Route::get('/health', fn() => response()->json(['status' => 'ok', 'time' => now()]));

// Rutas protegidas por sanctum (ejemplo de usuario autenticado)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| Competidores
|--------------------------------------------------------------------------
*/
Route::controller(CompetidorController::class)->group(function () {
    Route::get('/competidores', 'index');
    Route::post('/competidores', 'store');
    Route::put('/competidores/{id}', 'update');   // corregido {id}, no {ci}
    Route::delete('/competidores/{id}', 'destroy');
});

/*
|--------------------------------------------------------------------------
| Tiempos
|--------------------------------------------------------------------------
*/
Route::prefix('tiempos')->controller(TiempoController::class)->group(function () {
    Route::post('/batch', 'storeBatch');
    Route::get('/etapa/{etapa}', 'porEtapa');
    Route::get('/general', 'clasificacionGeneral');
});

/*
|--------------------------------------------------------------------------
| Eventos
|--------------------------------------------------------------------------
*/
Route::controller(EventoController::class)->group(function () {
    Route::get('/eventos', 'index');
    Route::post('/eventos', 'store');
    Route::get('/eventos/{id}', 'show');
    Route::put('/eventos/{id}', 'update');   // asegúrate que esté implementado en tu controller
    Route::delete('/eventos/{id}', 'destroy');
});

/*
|--------------------------------------------------------------------------
| Cronómetros
|--------------------------------------------------------------------------
*/
Route::prefix('cronometro')->controller(CronometroController::class)->group(function () {
    Route::post('/iniciar', 'iniciar');
    Route::get('/tiempo-actual', 'tiempoActual');
    Route::post('/detener', 'detener');
});
