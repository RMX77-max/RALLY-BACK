<?php
namespace App\Http\Controllers;

use App\Models\{Banner, Categoria, Cronograma, Equipo, Etapa, Evento, Recorrido, Resultado};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminContenidoRallyController extends Controller
{
    public function guardarEvento(Request $request, Evento $evento)
    {
        $data = $request->validate([
            'nombre' => 'sometimes|required|string|max:255', 'edicion' => 'nullable|integer|min:2000|max:2100',
            'fecha' => 'nullable|date', 'fecha_fin' => 'nullable|date|after_or_equal:fecha', 'ubicacion' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string', 'lema' => 'nullable|string|max:255', 'texto_introductorio' => 'nullable|string',
            'estado' => ['nullable', Rule::in(['borrador', 'publicado', 'finalizado'])], 'activo' => 'boolean',
        ]);
        if (!empty($data['activo'])) Evento::where('id', '!=', $evento->id)->update(['activo' => false]);
        $evento->update($data);
        return response()->json(['data' => $evento->fresh()]);
    }

    public function categorias(Evento $evento) { return $evento->categorias()->orderBy('orden')->get(); }
    public function guardarCategoria(Request $request, Evento $evento, ?Categoria $categoria = null)
    {
        $data = $request->validate(['nombre' => 'required|string|max:100', 'nombre_corto' => 'nullable|string|max:50', 'descripcion' => 'nullable|string', 'imagen' => 'nullable|string', 'icono' => 'nullable|string|max:50', 'color' => 'nullable|string|max:20', 'orden' => 'integer|min:0', 'activa' => 'boolean']);
        $model = $categoria?->exists ? tap($categoria)->update($data) : $evento->categorias()->create($data);
        return response()->json(['data' => $model->fresh()], $categoria?->exists ? 200 : 201);
    }

    public function eliminarCategoria(Evento $evento, Categoria $categoria)
    {
        abort_unless($categoria->evento_id === $evento->id, 404);
        abort_if($categoria->competidores()->exists(), 422, 'No se puede eliminar una categoría que tiene pilotos asignados.');
        $categoria->delete();
        return response()->json(['message' => 'Categoría eliminada correctamente.']);
    }

    public function equipos(Evento $evento) { return $evento->hasMany(Equipo::class)->orderBy('orden')->get(); }
    public function guardarEquipo(Request $request, Evento $evento, ?Equipo $equipo = null)
    {
        $data = $request->validate(['nombre' => 'required|string|max:150', 'logo' => 'nullable|string', 'url' => 'nullable|url', 'orden' => 'integer|min:0', 'activo' => 'boolean']);
        $model = $equipo?->exists ? tap($equipo)->update($data) : Equipo::create($data + ['evento_id' => $evento->id]);
        return response()->json(['data' => $model->fresh()], $equipo?->exists ? 200 : 201);
    }

    public function etapas(Evento $evento) { return $evento->etapas()->orderBy('orden')->orderBy('numero')->get(); }
    public function guardarEtapa(Request $request, Evento $evento, ?Etapa $etapa = null)
    {
        if (is_string($request->hora) && preg_match('/^\d{2}:\d{2}:\d{2}$/', $request->hora)) {
            $request->merge(['hora' => substr($request->hora, 0, 5)]);
        }
        $data = $request->validate(['numero' => 'required|integer|min:1', 'nombre' => 'required|string|max:150', 'origen' => 'nullable|string|max:150', 'destino' => 'nullable|string|max:150', 'distancia_km' => 'nullable|numeric|min:0', 'fecha' => 'nullable|date', 'hora' => 'nullable|date_format:H:i', 'descripcion' => 'nullable|string', 'imagen' => 'nullable|string', 'orden' => 'integer|min:0', 'estado' => Rule::in(['programada', 'en_disputa', 'completada', 'cancelada'])]);
        $model = $etapa?->exists ? tap($etapa)->update($data) : $evento->etapas()->create($data);
        return response()->json(['data' => $model->fresh()], $etapa?->exists ? 200 : 201);
    }

    public function eliminarEtapa(Evento $evento, Etapa $etapa)
    {
        abort_unless($etapa->evento_id === $evento->id, 404);
        $etapa->delete();
        return response()->json(['message' => 'Etapa eliminada correctamente.']);
    }

    public function guardarCronograma(Request $request, Evento $evento, ?Cronograma $cronograma = null)
    {
        if ($cronograma?->exists) {
            abort_unless($cronograma->evento_id === $evento->id, 404);
        }
        $data = $request->validate(['fecha' => 'required|date', 'hora' => 'nullable|date_format:H:i', 'actividad' => 'required|string|max:200', 'ubicacion' => 'nullable|string|max:200', 'descripcion' => 'nullable|string', 'orden' => 'integer|min:0', 'activo' => 'boolean']);
        $model = $cronograma?->exists ? tap($cronograma)->update($data) : $evento->cronograma()->create($data);
        return response()->json(['data' => $model->fresh()], $cronograma?->exists ? 200 : 201);
    }

    public function eliminarCronograma(Evento $evento, Cronograma $cronograma)
    {
        abort_unless($cronograma->evento_id === $evento->id, 404);
        $cronograma->delete();
        return response()->json(['message' => 'Actividad eliminada correctamente.']);
    }

    public function guardarBanner(Request $request, Evento $evento, ?Banner $banner = null)
    {
        $data = $request->validate(['seccion' => 'required|string|max:50', 'titulo' => 'nullable|string|max:200', 'subtitulo' => 'nullable|string|max:255', 'imagen_escritorio' => 'required|string', 'imagen_movil' => 'nullable|string', 'texto_boton' => 'nullable|string|max:80', 'enlace_boton' => 'nullable|string|max:255', 'orden' => 'integer|min:0', 'activo' => 'boolean']);
        $model = $banner?->exists ? tap($banner)->update($data) : $evento->banners()->create($data);
        return response()->json(['data' => $model->fresh()], $banner?->exists ? 200 : 201);
    }

    public function guardarRecorrido(Request $request, Evento $evento)
    {
        $url = trim((string) $request->input('url_google_maps', ''));
        if (preg_match('/<iframe[^>]+src=["\']([^"\']+)["\']/i', $url, $match)) {
            $url = html_entity_decode($match[1]);
        }
        if ($url !== '' && !preg_match('/^https?:\/\//i', $url)) {
            $url = 'https://'.$url;
        }
        $request->merge(['url_google_maps' => $url ?: null]);

        $data = $request->validate([
            'imagen_mapa' => 'nullable|string|max:2048',
            'url_google_maps' => 'nullable|url:http,https|max:2048',
            'archivo_recorrido' => 'nullable|string|max:2048',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean',
        ]);
        $anterior = Recorrido::where('evento_id', $evento->id)->value('imagen_mapa');
        $recorrido = Recorrido::updateOrCreate(['evento_id' => $evento->id], $data);
        if ($anterior && !empty($data['imagen_mapa']) && $anterior !== $data['imagen_mapa'] && !preg_match('/^https?:\/\//i', $anterior)) {
            Storage::disk('public')->delete($anterior);
        }
        return response()->json(['data' => $recorrido]);
    }

    public function publicarResultados(Request $request, Evento $evento)
    {
        $data = $request->validate(['etapa_id' => 'required|exists:etapas,id', 'publicacion' => ['required', Rule::in(['borrador', 'provisional', 'oficial', 'oculto'])]]);
        Resultado::where('evento_id', $evento->id)->where('etapa_id', $data['etapa_id'])->update(['publicacion' => $data['publicacion']]);
        return response()->json(['message' => 'Estado de resultados actualizado.']);
    }
}
