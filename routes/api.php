<?php

use Illuminate\Http\Request;
use App\Http\Controllers\TiempoController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompetidorController;
use App\Http\Controllers\EventoController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/competidores', [CompetidorController::class, 'store']);

Route::get('/competidores', [CompetidorController::class, 'index']);

Route::post('/tiempos/batch', [TiempoController::class, 'storeBatch']);
Route::get('/tiempos/etapa/{etapa}', [TiempoController::class, 'porEtapa']);
Route::put('/competidores/{ci}', [CompetidorController::class, 'update']);
Route::delete('/competidores/{ci}', [CompetidorController::class, 'destroy']);
Route::get('/tiempos/general', [TiempoController::class, 'clasificacionGeneral']);




Route::get('/eventos', [EventoController::class, 'index']);
Route::post('/eventos', [EventoController::class, 'store']);
Route::get('/eventos/{id}', [EventoController::class, 'show']);
Route::put('/eventos/{id}', [EventoController::class, 'update']);
Route::delete('/eventos/{id}', [EventoController::class, 'destroy']);
