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
            ['inicio' => now(), 'activo' => true]
        );

        return response()->json(['success' => true, 'cronometro' => $cronometro]);
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
        $ahora = Carbon::now();
        $diferencia = $inicio->diff($ahora);

        return response()->json([
            'success' => true,
            'inicio' => $inicio->toDateTimeString(),
            'tiempo_actual' => sprintf('%02d:%02d:%02d', $diferencia->h, $diferencia->i, $diferencia->s),
        ]);
    }

    // Reiniciar o detener cronómetro
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
            $cronometro->activo = false;
            $cronometro->save();
        }

        return response()->json(['success' => true, 'cronometro' => $cronometro]);
    }
}
