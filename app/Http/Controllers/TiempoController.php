<?php

namespace App\Http\Controllers;

use App\Models\Tiempo;
use App\Models\Competidor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TiempoController extends Controller
{
    // Guardar múltiples tiempos (batch)
    public function storeBatch(Request $request)
{
    $validator = Validator::make($request->all(), [
        'etapa' => 'required|integer|between:1,6',
        'tiempos' => 'required|array|min:1',
        'tiempos.*.competidor_ci' => 'required|exists:competidores,ci',
        'tiempos.*.tiempo' => 'required|date_format:H:i:s',
        'tiempos.*.fecha' => 'required|date'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors()
        ], 422);
    }

    DB::beginTransaction();
    try {
        $savedTimes = [];
        foreach ($request->tiempos as $timeData) {
            $time = Tiempo::create([
                'competidor_ci' => $timeData['competidor_ci'],
                'etapa' => $request->etapa,
                'tiempo' => $timeData['tiempo'],
                'fecha_registro' => Carbon::parse($timeData['fecha'])->format('Y-m-d H:i:s')
            ]);
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
            'message' => 'Error al guardar tiempos'
        ], 500);
    }
}

    // Obtener tiempos por etapa
    public function porEtapa($etapa)
    {
        $tiempos = Tiempo::with('competidor')
            ->where('etapa', $etapa)
            ->orderBy('tiempo', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $tiempos
        ]);
    }
}
