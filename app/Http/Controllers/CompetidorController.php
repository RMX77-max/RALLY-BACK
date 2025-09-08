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
                'ci' => 'required|unique:competidores',
                'nombre' => 'required',
                'ciudad' => 'required',
                'categoria' => 'required',
                'numeral' => 'required|numeric',
                'evento_id' => 'required|exists:eventos,id',
            ]);

            $fotoPath = null;
            if ($request->hasFile('foto')) {
                $fotoPath = $request->file('foto')->store('fotos_competidores', 'public');
            }

            $competidor = Competidor::create([
                'ci' => $validated['ci'],
                'nombre' => $validated['nombre'],
                'ciudad' => $validated['ciudad'],
                'team' => $request->team,
                'categoria' => $validated['categoria'],
                'numeral' => $validated['numeral'],
                'tipodesangre' => $request->tipodesangre,
                'foto_path' => $fotoPath,
                'evento_id' => $validated['evento_id']
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

   public function update(Request $request, $ci)
{
    $competidor = Competidor::findOrFail($ci);

    // Verifica que el evento_id del competidor coincida con el que se está enviando
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


   public function destroy(Request $request, $ci)
{
    $competidor = Competidor::findOrFail($ci);

    // Verifica que el evento_id del competidor coincida con el que se está enviando
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
