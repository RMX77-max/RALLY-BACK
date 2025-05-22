<?php

use Illuminate\Http\Request;
use App\Http\Controllers\TiempoController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompetidorController;

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
