<?php

namespace App\Http\Controllers;

use App\Models\Competidor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CompetidorController extends Controller
{

    public function store(Request $request)
{
    try {
        Log::info('Datos recibidos:', $request->all());

        $validated = $request->validate([
            'nombre' => 'required',
            'ciudad' => 'required',
            'categoria' => 'required',
            'numeral' => [
    'required',
    'numeric',
    'unique:competidores,numeral,NULL,id,evento_id,' . $request->evento_id
],

            'evento_id' => 'required|exists:eventos,id',
        ]);

        // Recuperar el evento para ver su tipo
        $evento = \App\Models\Evento::findOrFail($validated['evento_id']);

        // Guardar foto si existe
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('fotos_competidores', 'public');
        }

        // Lógica de orden de largada
        $ordenLargada = null;

        if (!$evento->grande) {

            // Orden secuencial por inscripción
            $lastOrder = Competidor::where('evento_id', $evento->id)->max('orden_largada');
            $ordenLargada = $lastOrder ? $lastOrder + 1 : 1;

        }elseif ($evento->grande) {

            // Verificar cupos
            $cupoCategoria = \App\Models\CupoCategoria::where('evento_id', $evento->id)
                ->where('categoria', $validated['categoria'])
                ->first();

            if (!$cupoCategoria) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay cupo configurado para esta categoría'
                ], 400);
            }

            $inscritos = Competidor::where('evento_id', $evento->id)
                ->where('categoria', $validated['categoria'])
                ->count();

            if ($inscritos >= $cupoCategoria->cupos) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya no hay cupos disponibles en esta categoría'
                ], 400);
            }

            // Orden largada = correlativo en el evento
            $lastOrder = Competidor::where('evento_id', $evento->id)->max('orden_largada');
            $ordenLargada = $lastOrder ? $lastOrder + 1 : 1;
        }

        // Crear competidor
        $competidor = Competidor::create([
            'nombre' => $validated['nombre'],
            'ciudad' => $validated['ciudad'],
            'team' => $request->team,
            'categoria' => $validated['categoria'],
            'numeral' => $validated['numeral'],
            'foto_path' => $fotoPath,
            'evento_id' => $validated['evento_id'],
            'orden_largada' => $ordenLargada,
        ]);

        return response()->json([
            'success' => true,
            'data' => $competidor
        ], 201);

    } catch (\Exception $e) {
        Log::error('Error en store: '.$e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error interno: '.$e->getMessage()
        ], 500);
    }
}


    public function index(Request $request)
    {
        try {
            $query = Competidor::query();

            if ($request->filled('evento_id')) {
                $query->where('evento_id', $request->evento_id);
            } else {
                return response()->json([
                    'error' => 'Debe enviar evento_id en la peticion'
                ], 400);
            }

            if ($request->filled('categoria')) {
                $query->where('categoria', $request->categoria);
            }

            $sortField = $request->input('sort_field', 'numeral');
            $sortDirection = $request->input('sort_direction', 'asc');

            $query->orderBy($sortField, $sortDirection);

            return $query->paginate($request->per_page ?? 10);

        } catch (\Exception $e) {
            Log::error('Error en CompetidorController@index: '.$e->getMessage());
            return response()->json([
                'error' => 'Error al filtrar competidores',
                'details' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }

  public function update(Request $request, $id)
{
    $competidor = Competidor::findOrFail($id);

    if ($request->filled('evento_id') && $competidor->evento_id != $request->evento_id) {
        return response()->json([
            'success' => false,
            'message' => 'No tienes permiso para actualizar este competidor (evento no coincide)',
        ], 403);
    }

    $competidor->update($request->all());

    return response()->json([
        'success' => true,
        'data' => $competidor,
    ]);
}

public function destroy(Request $request, $id)
{
    $competidor = Competidor::findOrFail($id);

    if ($request->filled('evento_id') && $competidor->evento_id != $request->evento_id) {
        return response()->json([
            'success' => false,
            'message' => 'No tienes permiso para eliminar este competidor (evento no coincide)',
        ], 403);
    }

    $competidor->delete();

    return response()->json(['success' => true]);
}


}
