<?php

namespace App\Http\Controllers;

use App\Models\CompetidorEvento;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CompetidoresImport;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CompetidorEventoController extends Controller
{
    // ============================================
    // 📥 Importar desde Excel
    // ============================================
    public function importarExcel(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls'
        ]);

        try {
            Excel::import(new CompetidoresImport, $request->file('archivo'));
            return response()->json(['message' => 'Competidores importados correctamente.'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al importar: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================
    // 🧾 Listar todos los competidores
    // ============================================
    public function index()
    {
        return CompetidorEvento::orderBy('numero', 'asc')->get();
    }

    // ============================================
    // ➕ Crear nuevo competidor (form manual)
    // ============================================
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'categoria' => 'nullable|string|max:100',
            'numero' => 'required|integer',
            'team' => 'nullable|string|max:100',
            'foto' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:4096',
        ]);

        $competidor = new CompetidorEvento($validated);

        // ✅ Procesar foto si se envía directamente
        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('fotos_competidores', 'public');
            $competidor->foto = asset('storage/' . $path);
        }

        $competidor->save();

        return response()->json([
            'message' => 'Competidor agregado correctamente.',
            'competidor' => $competidor
        ], 201);
    }

    // ============================================
    // ✏️ Actualizar competidor existente
    // ============================================
    public function update(Request $request, $id)
    {
        $competidor = CompetidorEvento::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'categoria' => 'nullable|string|max:100',
            'numero' => 'sometimes|required|integer',
            'team' => 'nullable|string|max:100',
            'foto' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:4096',
        ]);

        $competidor->fill($validated);

        // ✅ Si viene una nueva foto, reemplazar la anterior
        if ($request->hasFile('foto')) {
            // Eliminar la vieja si existe
            if ($competidor->foto) {
                $relative = Str::after($competidor->foto, '/storage/');
                if (Storage::disk('public')->exists($relative)) {
                    Storage::disk('public')->delete($relative);
                }
            }

            $path = $request->file('foto')->store('fotos_competidores', 'public');
            $competidor->foto = asset('storage/' . $path);
        }

        $competidor->save();

        return response()->json([
            'message' => 'Competidor actualizado correctamente.',
            'competidor' => $competidor
        ]);
    }

    // ============================================
    // 🖼️ Subir o actualizar solo la foto (ruta auxiliar)
    // ============================================
    public function subirFoto(Request $request, $id)
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpeg,jpg,png,webp|max:4096'
        ]);

        $competidor = CompetidorEvento::findOrFail($id);

        if ($competidor->foto) {
            $relative = Str::after($competidor->foto, '/storage/');
            if (Storage::disk('public')->exists($relative)) {
                Storage::disk('public')->delete($relative);
            }
        }

        $path = $request->file('foto')->store('fotos_competidores', 'public');
        $competidor->foto = asset('storage/' . $path);
        $competidor->save();

        return response()->json([
            'message' => 'Foto actualizada correctamente.',
            'foto' => $competidor->foto
        ]);
    }

    // ============================================
    // 🗑️ Eliminar competidor
    // ============================================
    public function destroy($id)
{
    $competidor = CompetidorEvento::findOrFail($id);

    if ($competidor->foto && Storage::disk('public')->exists($competidor->foto)) {
        Storage::disk('public')->delete($competidor->foto);
    }

    $competidor->delete();

    return response()->json(['message' => 'Competidor eliminado correctamente.']);
}

}
