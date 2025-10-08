<?php

namespace App\Http\Controllers;

use App\Models\Patrocinador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PatrocinadorController extends Controller
{
    // 🔹 Listar todos los patrocinadores (público)
    public function index()
    {
        return Patrocinador::orderByDesc('id')
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'titulo' => $p->titulo,
                'descripcion' => $p->descripcion,
                'imagen' => $p->imagen ? asset('storage/' . $p->imagen) : null,
            ]);
    }

    // 🔹 Crear nuevo patrocinador (solo admin)
    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'imagen' => 'nullable|mimes:jpg,jpeg,png,webp|max:8192',
        ]);

        $path = null;
        if ($request->hasFile('imagen')) {
            $path = $request->file('imagen')->store('patrocinadores', 'public');
        }

        $patrocinador = Patrocinador::create([
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'imagen' => $path,
        ]);

        return response()->json([
            'message' => 'Patrocinador creado correctamente',
            'patrocinador' => [
                'id' => $patrocinador->id,
                'titulo' => $patrocinador->titulo,
                'descripcion' => $patrocinador->descripcion,
                'imagen' => $patrocinador->imagen ? asset('storage/' . $patrocinador->imagen) : null,
            ],
        ]);
    }

    // 🔹 Actualizar patrocinador (solo admin)
    public function update(Request $request, $id)
    {
        $patrocinador = Patrocinador::findOrFail($id);

        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'imagen' => 'nullable|mimes:jpg,jpeg,png,webp|max:8192',
        ]);

        // Si se sube una nueva imagen, eliminar la anterior
        if ($request->hasFile('imagen')) {
            if ($patrocinador->imagen) {
                Storage::disk('public')->delete($patrocinador->imagen);
            }
            $path = $request->file('imagen')->store('patrocinadores', 'public');
            $patrocinador->imagen = $path;
        }

        $patrocinador->update([
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
        ]);

        $patrocinador->save();

        return response()->json([
            'message' => 'Patrocinador actualizado correctamente',
            'patrocinador' => [
                'id' => $patrocinador->id,
                'titulo' => $patrocinador->titulo,
                'descripcion' => $patrocinador->descripcion,
                'imagen' => $patrocinador->imagen ? asset('storage/' . $patrocinador->imagen) : null,
            ],
        ]);
    }

    // 🔹 Eliminar patrocinador (solo admin)
    public function destroy($id)
    {
        $patrocinador = Patrocinador::findOrFail($id);
        if ($patrocinador->imagen) {
            Storage::disk('public')->delete($patrocinador->imagen);
        }
        $patrocinador->delete();

        return response()->json(['message' => 'Patrocinador eliminado correctamente']);
    }
}
