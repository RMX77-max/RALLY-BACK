<?php
namespace App\Http\Controllers;

use App\Models\{Categoria, Competidor, Equipo, Evento};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PilotoRallyController extends Controller
{
    public function index(Request $request)
    {
        $eventId = $request->integer('evento_id') ?: Evento::where('activo', true)->value('id');
        return Competidor::with(['categoriaRelacion:id,nombre', 'equipo:id,nombre,logo'])
            ->where('evento_id', $eventId)->where('estado', 'publicado')
            ->orderByDesc('destacado')->orderBy('orden')->orderBy('numeral')->get()
            ->map(fn ($p) => [
                'id' => $p->id, 'nombre' => $p->nombre, 'numero' => $p->numeral,
                'categoria' => $p->categoriaRelacion?->nombre ?? $p->categoria,
                'team' => $p->equipo?->nombre ?? $p->team, 'vehiculo' => $p->vehiculo,
                'ciudad' => $p->ciudad, 'pais' => $p->pais, 'biografia' => $p->biografia,
                'frase' => $p->frase, 'destacado' => $p->destacado, 'foto' => $p->foto_path, 'foto_url' => $p->foto_url,
            ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePilot($request);
        $data['evento_id'] ??= Evento::where('activo', true)->value('id');
        $data = $this->relations($data);
        if ($request->hasFile('foto')) $data['foto_path'] = $request->file('foto')->store('fotos_competidores', 'public');
        $pilot = Competidor::create($data);
        return response()->json(['message' => 'Piloto registrado.', 'competidor' => $pilot->fresh()], 201);
    }

    public function update(Request $request, Competidor $competidor)
    {
        $data = $this->validatePilot($request, true);
        $data['evento_id'] ??= $competidor->evento_id;
        $data = $this->relations($data);
        if ($request->hasFile('foto')) {
            if ($competidor->foto_path) Storage::disk('public')->delete($competidor->foto_path);
            $data['foto_path'] = $request->file('foto')->store('fotos_competidores', 'public');
        }
        $competidor->update($data);
        return response()->json(['message' => 'Piloto actualizado.', 'competidor' => $competidor->fresh()]);
    }

    public function destroy(Competidor $competidor)
    {
        if ($competidor->foto_path) Storage::disk('public')->delete($competidor->foto_path);
        $competidor->delete();
        return response()->json(['message' => 'Piloto eliminado.']);
    }

    private function validatePilot(Request $request, bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';
        return $request->validate([
            'evento_id' => 'nullable|exists:eventos,id', 'nombre' => "$required|string|max:255",
            'numero' => "$required|integer|min:1", 'categoria' => "$required|string|max:100",
            'team' => "$required|string|max:150", 'vehiculo' => 'nullable|string|max:150',
            'ciudad' => 'nullable|string|max:150', 'pais' => 'nullable|string|max:100', 'biografia' => 'nullable|string',
            'frase' => 'nullable|string|max:255', 'destacado' => 'boolean', 'orden' => 'integer|min:0',
            'estado' => 'nullable|in:borrador,publicado,inactivo', 'foto' => 'nullable|image|max:4096',
        ]);
    }

    private function relations(array $data): array
    {
        if (isset($data['numero'])) { $data['numeral'] = $data['numero']; unset($data['numero']); }
        if (!empty($data['categoria']) && !empty($data['evento_id'])) {
            $category = Categoria::firstOrCreate(['evento_id' => $data['evento_id'], 'nombre' => $data['categoria']]);
            $data['categoria_id'] = $category->id;
        }
        if (!empty($data['team']) && !empty($data['evento_id'])) {
            $team = Equipo::firstOrCreate(['evento_id' => $data['evento_id'], 'nombre' => $data['team']]);
            $data['equipo_id'] = $team->id;
        }
        $data['ciudad'] ??= 'Sin registrar'; $data['estado'] ??= 'publicado';
        unset($data['foto']);
        return $data;
    }
}
