<?php

// app/Http/Controllers/GaleriaController.php
namespace App\Http\Controllers;

use App\Models\FotoRally;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GaleriaController extends Controller
{
    // 🔹 Listar todas las fotos
    public function index()
    {
        return FotoRally::orderByDesc('id')
            ->get()
            ->map(fn($f) => [
                'id' => $f->id,
                'url' => asset('storage/' . $f->ruta),
            ]);
    }

    // 🔹 Subir nueva foto
    public function store(Request $request)
    {
        $request->validate([
    'foto' => 'required|mimes:jpg,jpeg,png,gif,webp|max:8192',
]);


        $path = $request->file('foto')->store('fotos_rally', 'public');

        $foto = FotoRally::create(['ruta' => $path]);

        return response()->json([
            'message' => 'Foto subida correctamente',
            'foto' => [
                'id' => $foto->id,
                'url' => asset('storage/' . $foto->ruta),
            ],
        ]);
    }

    // 🔹 Eliminar una foto
    public function destroy($id)
    {
        $foto = FotoRally::findOrFail($id);
        Storage::disk('public')->delete($foto->ruta);
        $foto->delete();

        return response()->json(['message' => 'Foto eliminada']);
    }
}

