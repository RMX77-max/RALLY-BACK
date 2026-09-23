<?php
namespace App\Http\Controllers;

use App\Models\{Evento, Galeria, Patrocinador, Video};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MultimediaRallyController extends Controller
{
    public function galeria(Request $request)
    {
        $eventId = $request->integer('evento_id') ?: Evento::where('activo', true)->value('id');
        return Galeria::where(fn ($q) => $q->where('evento_id', $eventId)->orWhereNull('evento_id'))
            ->where('publicada', true)->orderByDesc('destacada')->orderBy('orden')->latest()->get()
            ->map(fn ($f) => $this->fotoResponse($f));
    }

    public function videos(Request $request)
    {
        $eventId = $request->integer('evento_id') ?: Evento::where('activo', true)->value('id');
        return Video::where(fn ($q) => $q->where('evento_id', $eventId)->orWhereNull('evento_id'))
            ->where('publicado', true)->orderByDesc('destacado')->orderBy('orden')->get();
    }

    public function patrocinadores(Request $request)
    {
        $eventId = $request->integer('evento_id') ?: Evento::where('activo', true)->value('id');
        return Patrocinador::where(fn ($q) => $q->where('evento_id', $eventId)->orWhereNull('evento_id'))
            ->where('activo', true)->orderBy('orden')->get()->map(fn ($p) => $p->setAttribute('imagen_url', $p->imagen ? asset('storage/'.ltrim($p->imagen, '/')) : null));
    }

    public function guardarFoto(Request $request)
    {
        $data = $request->validate(['evento_id' => 'required|exists:eventos,id', 'titulo' => 'nullable|string|max:255', 'descripcion' => 'nullable|string', 'categoria' => 'nullable|string|max:80', 'autor' => 'nullable|string|max:150', 'fecha_captura' => 'nullable|date', 'destacada' => 'boolean', 'orden' => 'integer|min:0', 'publicada' => 'boolean', 'foto' => 'required|image|max:10240']);
        $data['imagen'] = $request->file('foto')->store('galeria', 'public'); unset($data['foto']);
        $photo = Galeria::create($data);
        return response()->json(['data' => $this->fotoResponse($photo)], 201);
    }

    public function eliminarFoto(Galeria $galeria)
    {
        Storage::disk('public')->delete($galeria->imagen); $galeria->delete();
        return response()->json(['message' => 'Foto eliminada.']);
    }

    public function guardarVideo(Request $request)
    {
        $data = $request->validate(['evento_id' => 'required|exists:eventos,id', 'titulo' => 'required|string|max:255', 'descripcion' => 'nullable|string', 'url' => 'required|url', 'miniatura' => 'nullable|string', 'duracion' => 'nullable|string|max:20', 'destacado' => 'boolean', 'orden' => 'integer|min:0', 'publicado' => 'boolean']);
        return response()->json(['data' => Video::create($data)], 201);
    }

    public function eliminarVideo(Video $video) { $video->delete(); return response()->json(['message' => 'Video eliminado.']); }

    private function fotoResponse(Galeria $photo): array
    {
        return ['id' => $photo->id, 'evento_id' => $photo->evento_id, 'titulo' => $photo->titulo, 'descripcion' => $photo->descripcion, 'categoria' => $photo->categoria, 'autor' => $photo->autor, 'destacada' => $photo->destacada, 'orden' => $photo->orden, 'imagen' => $photo->imagen, 'url' => asset('storage/'.ltrim($photo->imagen, '/'))];
    }
}
