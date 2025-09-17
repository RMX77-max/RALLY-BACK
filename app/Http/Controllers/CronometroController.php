<?php

namespace App\Http\Controllers;

use App\Models\Cronometro;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CronometroController extends Controller
{
    // Iniciar cronómetro para una etapa
    public function iniciar(Request $request)
    {
        $request->validate([
            'evento_id' => 'required|exists:eventos,id',
            'etapa' => 'required|integer|between:1,6',
        ]);

        $cronometro = Cronometro::updateOrCreate(
            ['evento_id' => $request->evento_id, 'etapa' => $request->etapa],
            ['inicio' => Carbon::now(), 'activo' => true]
        );

        return response()->json([
            'success' => true,
            'message' => 'Cronómetro iniciado',
            'data' => $cronometro
        ]);
    }

    // Obtener el tiempo actual de una etapa
    public function tiempoActual(Request $request)
    {
        $request->validate([
            'evento_id' => 'required|exists:eventos,id',
            'etapa' => 'required|integer|between:1,6',
        ]);

        $cronometro = Cronometro::where('evento_id', $request->evento_id)
            ->where('etapa', $request->etapa)
            ->first();

        if (!$cronometro || !$cronometro->activo || !$cronometro->inicio) {
            return response()->json([
                'success' => false,
                'message' => 'No hay cronómetro activo para esta etapa',
            ], 404);
        }

        $inicio = Carbon::parse($cronometro->inicio);
        $elapsedSeconds = $inicio->diffInSeconds(Carbon::now());

        return response()->json([
            'success' => true,
            'data' => [
                'inicio' => $inicio->toDateTimeString(),
                'tiempo_actual' => gmdate('H:i:s', $elapsedSeconds),
                'segundos' => $elapsedSeconds,
            ]
        ]);
    }

    // Detener cronómetro
    public function detener(Request $request)
    {
        $request->validate([
            'evento_id' => 'required|exists:eventos,id',
            'etapa' => 'required|integer|between:1,6',
        ]);

        $cronometro = Cronometro::where('evento_id', $request->evento_id)
            ->where('etapa', $request->etapa)
            ->first();

        if ($cronometro) {
            $cronometro->update(['activo' => false]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cronómetro detenido',
            'data' => $cronometro
        ]);
    }

    // Resetear cronómetro
    public function reset(Request $request)
    {
        $request->validate([
            'evento_id' => 'required|exists:eventos,id',
            'etapa' => 'required|integer|between:1,6',
        ]);

        $cronometro = Cronometro::where('evento_id', $request->evento_id)
            ->where('etapa', $request->etapa)
            ->first();

        if ($cronometro) {
            $cronometro->update(['inicio' => null, 'activo' => false]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cronómetro reseteado',
            'data' => $cronometro
        ]);
    }
}
