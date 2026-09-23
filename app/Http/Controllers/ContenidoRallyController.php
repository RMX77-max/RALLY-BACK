<?php
namespace App\Http\Controllers;

use App\Models\{Evento, Resultado};
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ContenidoRallyController extends Controller
{
    public function eventoActivo()
    {
        $relations = [
            'categorias' => fn ($q) => $q->where('activa', true)->orderBy('orden'),
            'etapas' => fn ($q) => $q->orderBy('orden')->orderBy('numero'),
            'cronograma' => fn ($q) => $q->where('activo', true)->orderBy('fecha')->orderBy('hora'),
            'banners' => fn ($q) => $q->where('activo', true)->orderBy('orden'),
            'equipos' => fn ($q) => $q->where('activo', true)->orderBy('orden'), 'recorrido',
        ];
        $event = Evento::with($relations)->where('activo', true)->first()
            ?? Evento::with($relations)->latest('fecha')->first();
        return response()->json(['data' => $event]);
    }

    public function resultados(Request $request)
    {
        $eventId = $request->integer('evento_id') ?: Evento::where('activo', true)->value('id');
        $query = Resultado::with(['competidor:id,nombre,foto_path,team,vehiculo', 'etapa:id,numero,nombre'])
            ->where('evento_id', $eventId)->whereIn('publicacion', ['provisional', 'oficial']);
        if ($request->filled('etapa_id')) $query->where('etapa_id', $request->integer('etapa_id'));
        if ($request->filled('categoria')) $query->where('categoria', $request->input('categoria'));
        $results = $query->get();
        if ($request->filled('etapa_id')) return response()->json(['data' => $this->ordenarEtapa($results)]);

        $general = $results->groupBy('competidor_id')->map(function (Collection $rows) {
            $first = $rows->first();
            $valid = $rows->where('estado_carrera', 'finalizo')->whereNotNull('tiempo_ms');
            return [
                'competidor_id' => $first->competidor_id, 'numero' => $first->numero,
                'piloto' => $first->nombre_piloto, 'categoria' => $first->categoria,
                'team' => $first->competidor?->team, 'vehiculo' => $first->competidor?->vehiculo,
                'foto_url' => $first->competidor?->foto_url, 'etapas_completadas' => $valid->count(),
                'tiempo_total_ms' => $valid->sum(fn ($row) => $row->tiempo_total_ms),
                'estado' => $valid->count() === $rows->count() ? 'finalizo' : 'incidencia',
            ];
        })->sortBy([['etapas_completadas', 'desc'], ['tiempo_total_ms', 'asc']])->values();
        $leader = $general->first()['tiempo_total_ms'] ?? null;
        $general = $general->map(function ($row, $index) use ($leader) {
            $row['posicion'] = $index + 1;
            $difference = $leader === null ? null : $row['tiempo_total_ms'] - $leader;
            $row['tiempo_total'] = $this->formatMs($row['tiempo_total_ms']);
            $row['diferencia'] = $difference ? '+ '.$this->formatMs($difference) : '—';
            return $row;
        });
        return response()->json(['data' => $general]);
    }

    private function ordenarEtapa(Collection $rows): Collection
    {
        $ordered = $rows->sortBy(fn ($row) => $row->estado_carrera === 'finalizo' ? $row->tiempo_total_ms : PHP_INT_MAX)->values();
        $leader = $ordered->firstWhere('estado_carrera', 'finalizo')?->tiempo_total_ms;
        return $ordered->map(function ($row, $index) use ($leader) {
            $row->posicion = $row->estado_carrera === 'finalizo' ? $index + 1 : null;
            $row->diferencia = $leader && $row->tiempo_total_ms > $leader ? '+ '.$this->formatMs($row->tiempo_total_ms - $leader) : '—';
            return $row;
        });
    }

    private function formatMs(int $ms): string
    {
        $h = intdiv($ms, 3600000); $m = intdiv($ms % 3600000, 60000); $s = intdiv($ms % 60000, 1000);
        return $h ? sprintf('%d:%02d:%02d', $h, $m, $s) : sprintf('%d:%02d', $m, $s);
    }
}
