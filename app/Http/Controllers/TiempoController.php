<?php

namespace App\Http\Controllers;

use App\Models\Tiempo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TiempoController extends Controller
{
    public function storeBatch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'etapa' => 'required|integer|between:1,6',
            'evento_id' => 'required|exists:eventos,id',
            'tiempos' => 'required|array|min:1',
            'tiempos.*.competidor_ci' => 'required|exists:competidores,ci',
            'tiempos.*.tiempo' => 'required|date_format:H:i:s',
            'tiempos.*.fecha' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $eventoId = $request->evento_id;

        DB::beginTransaction();
        try {
            $savedTimes = [];

            foreach ($request->tiempos as $timeData) {
                $time = Tiempo::updateOrCreate(
                    [
                        'competidor_ci' => $timeData['competidor_ci'],
                        'etapa' => $request->etapa,
                        'evento_id' => $eventoId,
                    ],
                    [
                        'tiempo' => $timeData['tiempo'],
                        'fecha_registro' => Carbon::parse($timeData['fecha'])->format('Y-m-d H:i:s'),
                    ]
                );
                $savedTimes[] = $time;
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'data' => $savedTimes
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving times: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar tiempos',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function porEtapa(Request $request, $etapa)
{
    try {
        $eventoId = $request->query('evento_id');

        if (!$eventoId) {
            return response()->json([
                'success' => false,
                'message' => 'Se requiere evento_id en la consulta'
            ], 400);
        }

        if (!in_array($etapa, [1, 2, 3, 4, 5, 6])) {
            return response()->json([
                'success' => false,
                'message' => 'Etapa no válida'
            ], 400);
        }

        $tiempos = Tiempo::with('competidor')
            ->where('etapa', $etapa)
            ->where('evento_id', $eventoId)
            ->orderBy('tiempo', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $tiempos
        ]);
    } catch (\Exception $e) {
        Log::error('Error en porEtapa: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error interno del servidor'
        ], 500);
    }
}

public function clasificacionGeneral(Request $request)
{
    $eventoId = $request->query('evento_id');

    if (!$eventoId) {
        return response()->json([
            'success' => false,
            'message' => 'Se requiere evento_id en la consulta'
        ], 400);
    }

    try {
        $tiempos = Tiempo::with('competidor')
            ->where('evento_id', $eventoId)
            ->get();

        $clasificados = [];

        foreach ($tiempos as $tiempo) {
            $ci = $tiempo->competidor_ci;

            if (!isset($clasificados[$ci])) {
                $clasificados[$ci] = [
                    'ci' => $ci,
                    'nombre' => $tiempo->competidor->nombre,
                    'categoria' => $tiempo->competidor->categoria,
                    'team' => $tiempo->competidor->team,
                    'foto' => $tiempo->competidor->foto_path,
                    'totalSegundos' => 0,
                ];
            }

            // Convertimos HH:MM:SS a segundos
            [$h, $m, $s] = explode(':', $tiempo->tiempo);
            $segundos = ($h * 3600) + ($m * 60) + $s;

            $clasificados[$ci]['totalSegundos'] += $segundos;
        }

        // Convertimos a lista ordenada
        $resultado = collect($clasificados)
            ->map(function ($c) {
                $c['totalTiempo'] = gmdate('H:i:s', $c['totalSegundos']);
                return $c;
            })
            ->sortBy('totalSegundos')
            ->values()
            ->all();

        return response()->json([
            'success' => true,
            'data' => $resultado
        ]);
    } catch (\Exception $e) {
        Log::error('Error en clasificacionGeneral: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Error al calcular la clasificación general'
        ], 500);
    }
}


}
