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
            ->where('publicado', true)->orderByDesc('destacado')->orderBy('orden')->get()
            ->map(fn ($video) => $this->videoResponse($video));
    }

    public function administrarGaleria(Evento $evento)
    {
        return Galeria::where(fn ($q) => $q->where('evento_id', $evento->id)->orWhereNull('evento_id'))
            ->orderByDesc('destacada')->orderBy('orden')->latest()->get()
            ->map(fn ($foto) => $this->fotoResponse($foto));
    }

    public function administrarVideos(Evento $evento)
    {
        return Video::where(fn ($q) => $q->where('evento_id', $evento->id)->orWhereNull('evento_id'))
            ->orderByDesc('destacado')->orderBy('orden')->get()
            ->map(fn ($video) => $this->videoResponse($video));
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

    public function actualizarFoto(Request $request, Galeria $galeria)
    {
        $data = $request->validate(['evento_id' => 'sometimes|exists:eventos,id', 'titulo' => 'nullable|string|max:255', 'descripcion' => 'nullable|string', 'categoria' => 'nullable|string|max:80', 'autor' => 'nullable|string|max:150', 'fecha_captura' => 'nullable|date', 'destacada' => 'boolean', 'orden' => 'integer|min:0', 'publicada' => 'boolean', 'foto' => 'nullable|image|max:10240']);
        if ($request->hasFile('foto')) {
            if ($galeria->imagen) Storage::disk('public')->delete($galeria->imagen);
            $data['imagen'] = $request->file('foto')->store('galeria', 'public');
        }
        unset($data['foto']);
        $galeria->update($data);
        return response()->json(['data' => $this->fotoResponse($galeria->fresh())]);
    }

    public function eliminarFoto(Galeria $galeria)
    {
        Storage::disk('public')->delete($galeria->imagen); $galeria->delete();
        return response()->json(['message' => 'Foto eliminada.']);
    }

    public function guardarVideo(Request $request)
    {
        $data = $request->validate(['evento_id' => 'required|exists:eventos,id', 'titulo' => 'required|string|max:255', 'descripcion' => 'nullable|string', 'url' => 'required|url', 'miniatura' => 'nullable|string', 'duracion' => 'nullable|string|max:20', 'destacado' => 'boolean', 'orden' => 'integer|min:0', 'publicado' => 'boolean']);
        return response()->json(['data' => $this->videoResponse(Video::create($data))], 201);
    }

    public function actualizarVideo(Request $request, Video $video)
    {
        $data = $request->validate(['evento_id' => 'sometimes|exists:eventos,id', 'titulo' => 'sometimes|required|string|max:255', 'descripcion' => 'nullable|string', 'url' => 'sometimes|required|url', 'miniatura' => 'nullable|string|max:2048', 'duracion' => 'nullable|string|max:20', 'destacado' => 'boolean', 'orden' => 'integer|min:0', 'publicado' => 'boolean']);
        if (array_key_exists('miniatura', $data) && $data['miniatura'] !== $video->miniatura) {
            $this->eliminarMiniaturaLocal($video->miniatura);
        }
        $video->update($data);
        return response()->json(['data' => $this->videoResponse($video->fresh())]);
    }

    public function eliminarVideo(Video $video)
    {
        $this->eliminarMiniaturaLocal($video->miniatura);
        $video->delete();
        return response()->json(['message' => 'Video eliminado.']);
    }

    private function fotoResponse(Galeria $photo): array
    {
        return ['id' => $photo->id, 'evento_id' => $photo->evento_id, 'titulo' => $photo->titulo, 'descripcion' => $photo->descripcion, 'categoria' => $photo->categoria, 'autor' => $photo->autor, 'fecha_captura' => $photo->fecha_captura?->format('Y-m-d'), 'destacada' => $photo->destacada, 'orden' => $photo->orden, 'publicada' => $photo->publicada, 'imagen' => $photo->imagen, 'url' => asset('storage/'.ltrim($photo->imagen, '/'))];
    }

    private function videoResponse(Video $video): array
    {
        $thumbnail = $video->miniatura;
        if ($thumbnail && !preg_match('/^https?:\/\//i', $thumbnail)) $thumbnail = asset('storage/'.ltrim($thumbnail, '/'));
        return ['id' => $video->id, 'evento_id' => $video->evento_id, 'titulo' => $video->titulo, 'descripcion' => $video->descripcion, 'url' => $video->url, 'miniatura' => $video->miniatura, 'miniatura_url' => $thumbnail, 'duracion' => $video->duracion, 'destacado' => $video->destacado, 'orden' => $video->orden, 'publicado' => $video->publicado];
    }

    private function eliminarMiniaturaLocal(?string $miniatura): void
    {
        if ($miniatura && !preg_match('/^https?:\/\//i', $miniatura)) {
            Storage::disk('public')->delete($miniatura);
        }
    }
}
