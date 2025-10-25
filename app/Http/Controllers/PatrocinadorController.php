<?php

namespace App\Http\Controllers;

use App\Models\Patrocinador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PatrocinadorController extends Controller
{
    private function toRelative(?string $stored): ?string
    {
        if (!$stored) return null;
        $after = Str::after($stored, 'storage/');
        return $after === $stored ? $stored : $after;
    }

    private function urlFromPath(?string $path): ?string
    {
        return $path ? asset('storage/' . ltrim($path, '/')) : null;
    }

    private function ensureDirs(): void
    {
        Storage::disk('public')->makeDirectory('patrocinadores');
    }

    // 🔹 Listar todos
    public function index()
    {
        return Patrocinador::orderByDesc('id')->get()
            ->map(function ($p) {
                $rel = $this->toRelative($p->imagen);
                return [
                    'id'          => $p->id,
                    'titulo'      => $p->titulo,
                    'descripcion' => $p->descripcion,
                    'imagen'      => $rel,                    // path relativo en DB
                    'imagen_url'  => $this->urlFromPath($rel) // URL absoluta para el front
                ];
            });
    }

    // 🔹 Crear (admin)
    public function store(Request $request)
    {
        $request->validate([
            'titulo'      => 'required|string|max:255',
            'descripcion' => 'required|string',
            'imagen'      => 'nullable|mimes:jpg,jpeg,png,webp|max:8192',
        ]);

        $this->ensureDirs();

        $path = null;
        if ($request->hasFile('imagen')) {
            $path = $request->file('imagen')->store('patrocinadores', 'public');
        }

        $pat = Patrocinador::create([
            'titulo'      => $request->titulo,
            'descripcion' => $request->descripcion,
            'imagen'      => $path,
        ]);

        return response()->json([
            'message'      => 'Patrocinador creado correctamente',
            'patrocinador' => [
                'id'          => $pat->id,
                'titulo'      => $pat->titulo,
                'descripcion' => $pat->descripcion,
                'imagen'      => $pat->imagen,
                'imagen_url'  => $this->urlFromPath($pat->imagen),
            ],
        ]);
    }

    // 🔹 Actualizar (admin)
    public function update(Request $request, $id)
    {
        $pat = Patrocinador::findOrFail($id);

        $request->validate([
            'titulo'      => 'required|string|max:255',
            'descripcion' => 'required|string',
            'imagen'      => 'nullable|mimes:jpg,jpeg,png,webp|max:8192',
        ]);

        $pat->titulo      = $request->titulo;
        $pat->descripcion = $request->descripcion;

        $this->ensureDirs();

        if ($request->hasFile('imagen')) {
            if ($pat->imagen) {
                $rel = $this->toRelative($pat->imagen);
                if ($rel && Storage::disk('public')->exists($rel)) {
                    Storage::disk('public')->delete($rel);
                }
            }
            $path = $request->file('imagen')->store('patrocinadores', 'public');
            $pat->imagen = $path;
        }

        $pat->save();

        return response()->json([
            'message'      => 'Patrocinador actualizado correctamente',
            'patrocinador' => [
                'id'          => $pat->id,
                'titulo'      => $pat->titulo,
                'descripcion' => $pat->descripcion,
                'imagen'      => $pat->imagen,
                'imagen_url'  => $this->urlFromPath($pat->imagen),
            ],
        ]);
    }

    // 🔹 Eliminar (admin)
    public function destroy($id)
    {
        $pat = Patrocinador::findOrFail($id);

        if ($pat->imagen) {
            $rel = $this->toRelative($pat->imagen);
            if ($rel && Storage::disk('public')->exists($rel)) {
                Storage::disk('public')->delete($rel);
            }
        }

        $pat->delete();

        return response()->json(['message' => 'Patrocinador eliminado correctamente']);
    }
}
