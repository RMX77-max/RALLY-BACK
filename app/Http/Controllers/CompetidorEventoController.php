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
    // 📥 Importar desde Excel (NO TOCAR)
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

    // Helpers -------------- //

    /** Normaliza lo guardado en DB (absoluta/relativa) a un path relativo del disco public */
    private function toRelative(string $stored = null): ?string
    {
        if (!$stored) return null;
        // soporta valores tipo '/storage/...' o 'https://.../storage/...'
        $after = Str::after($stored, 'storage/');
        return $after === $stored ? $stored : $after;
    }

    /** Devuelve URL absoluta desde path relativo del disco public */
    private function urlFromPath(?string $path): ?string
    {
        return $path ? asset('storage/' . ltrim($path, '/')) : null;
    }

    /** Asegura que existan los directorios de fotos */
    private function ensureDirs(): void
    {
        Storage::disk('public')->makeDirectory('fotos_competidores');
    }

    // ============================================
    // 🧾 Listar todos los competidores
    // ============================================
    public function index()
    {
        return CompetidorEvento::orderBy('numero', 'asc')->get()
            ->map(function ($c) {
                $rel = $this->toRelative($c->foto);
                return [
                    'id'        => $c->id,
                    'nombre'    => $c->nombre,
                    'categoria' => $c->categoria,
                    'numero'    => $c->numero,
                    'team'      => $c->team,
                    'foto'      => $rel,                           // path relativo en DB
                    'foto_url'  => $this->urlFromPath($rel),       // URL absoluta para el front
                ];
            });
    }

    // ============================================
    // ➕ Crear nuevo competidor (form manual)
    // ============================================
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre'    => 'required|string|max:255',
            'categoria' => 'nullable|string|max:100',
            'numero'    => 'required|integer',
            'team'      => 'nullable|string|max:100',
            'foto'      => 'nullable|image|mimes:jpeg,jpg,png,webp|max:4096',
        ]);

        $this->ensureDirs();

        $competidor = new CompetidorEvento($validated);

        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('fotos_competidores', 'public');
            $competidor->foto = $path; // guarda solo relativo
        }

        $competidor->save();

        return response()->json([
            'message'    => 'Competidor agregado correctamente.',
            'competidor' => [
                'id'        => $competidor->id,
                'nombre'    => $competidor->nombre,
                'categoria' => $competidor->categoria,
                'numero'    => $competidor->numero,
                'team'      => $competidor->team,
                'foto'      => $competidor->foto,
                'foto_url'  => $this->urlFromPath($competidor->foto),
            ],
        ], 201);
    }

    // ============================================
    // ✏️ Actualizar competidor existente
    // ============================================
    public function update(Request $request, $id)
    {
        $competidor = CompetidorEvento::findOrFail($id);

        $validated = $request->validate([
            'nombre'    => 'sometimes|required|string|max:255',
            'categoria' => 'nullable|string|max:100',
            'numero'    => 'sometimes|required|integer',
            'team'      => 'nullable|string|max:100',
            'foto'      => 'nullable|image|mimes:jpeg,jpg,png,webp|max:4096',
        ]);

        $competidor->fill($validated);
        $this->ensureDirs();

        if ($request->hasFile('foto')) {
            // borra la anterior (soporta absoluta vieja)
            if ($competidor->foto) {
                $rel = $this->toRelative($competidor->foto);
                if ($rel && Storage::disk('public')->exists($rel)) {
                    Storage::disk('public')->delete($rel);
                }
            }
            $path = $request->file('foto')->store('fotos_competidores', 'public');
            $competidor->foto = $path;
        }

        $competidor->save();

        return response()->json([
            'message'    => 'Competidor actualizado correctamente.',
            'competidor' => [
                'id'        => $competidor->id,
                'nombre'    => $competidor->nombre,
                'categoria' => $competidor->categoria,
                'numero'    => $competidor->numero,
                'team'      => $competidor->team,
                'foto'      => $competidor->foto,
                'foto_url'  => $this->urlFromPath($competidor->foto),
            ],
        ]);
    }

    // ============================================
    // 🖼️ Subir o actualizar solo la foto
    // ============================================
    public function subirFoto(Request $request, $id)
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpeg,jpg,png,webp|max:4096'
        ]);

        $competidor = CompetidorEvento::findOrFail($id);
        $this->ensureDirs();

        if ($competidor->foto) {
            $rel = $this->toRelative($competidor->foto);
            if ($rel && Storage::disk('public')->exists($rel)) {
                Storage::disk('public')->delete($rel);
            }
        }

        $path = $request->file('foto')->store('fotos_competidores', 'public');
        $competidor->foto = $path;
        $competidor->save();

        return response()->json([
            'message' => 'Foto actualizada correctamente.',
            'foto'    => $competidor->foto,
            'foto_url'=> $this->urlFromPath($competidor->foto),
        ]);
    }

    // ============================================
    // 🗑️ Eliminar competidor
    // ============================================
    public function destroy($id)
    {
        $competidor = CompetidorEvento::findOrFail($id);

        if ($competidor->foto) {
            $rel = $this->toRelative($competidor->foto);
            if ($rel && Storage::disk('public')->exists($rel)) {
                Storage::disk('public')->delete($rel);
            }
        }

        $competidor->delete();

        return response()->json(['message' => 'Competidor eliminado correctamente.']);
    }
}
