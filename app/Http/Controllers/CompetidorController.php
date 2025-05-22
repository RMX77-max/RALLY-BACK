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
        Log::info('Datos recibidos:', $request->all()); // Registra los datos entrantes

        $validated = $request->validate([
            'ci' => 'required|unique:competidores',
            'nombre' => 'required',
            'ciudad' => 'required',
            'categoria' => 'required',
            'numeral' => 'required|numeric',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            Log::info('Archivo recibido:', [$request->file('foto')->getClientOriginalName()]);
            $fotoPath = $request->file('foto')->store('fotos_competidores', 'public');
            Log::info('Ruta de foto guardada:', [$fotoPath]);
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
        ]);

        Log::info('Competidor creado:', $competidor->toArray());

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

        // Filtro por ciudad
        if ($request->filled('ciudad')) {
            $query->where('ciudad', 'like', '%' . $request->ciudad . '%');
        }

        // Filtro por categoría (exacto)
        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }

    // Ordenando por numeral
    $sortField = $request->input('sort_field', 'numeral'); // Campo por defecto: 'numeral'
    $sortDirection = $request->input('sort_direction', 'asc'); // Orden por defecto: ascendente

    $query->orderBy($sortField, $sortDirection);

        return $query->paginate($request->per_page ?? 10);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error en CompetidorController@index: '.$e->getMessage());
        return response()->json([
            'error' => 'Error al filtrar competidores',
            'details' => env('APP_DEBUG') ? $e->getMessage() : null
        ], 500);
    }
}
}
