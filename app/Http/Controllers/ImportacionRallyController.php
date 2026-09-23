<?php
namespace App\Http\Controllers;

use App\Models\{Categoria, Competidor, Equipo, Importacion, Resultado};
use App\Services\ImportadorRally;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ImportacionRallyController extends Controller
{
    public function previsualizar(Request $request, ImportadorRally $parser)
    {
        $data = $request->validate([
            'tipo' => 'required|in:pilotos,resultados', 'evento_id' => 'required|exists:eventos,id',
            'etapa_id' => 'nullable|required_if:tipo,resultados|exists:etapas,id',
            'archivo' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240',
        ]);
        $parsed = $parser->leer($request->file('archivo'));
        $checked = $parser->validar($parsed['filas'], $data['tipo']);
        $import = Importacion::create([
            'evento_id' => $data['evento_id'], 'etapa_id' => $data['etapa_id'] ?? null,
            'user_id' => $request->user()?->id, 'tipo' => $data['tipo'],
            'nombre_original' => $request->file('archivo')->getClientOriginalName(),
            'archivo' => $request->file('archivo')->store('importaciones'), 'columnas' => $parsed['columnas'],
            'vista_previa' => $checked['filas'], 'errores' => $checked['errores'],
            'total_filas' => count($parsed['filas']), 'estado' => empty($checked['errores']) ? 'validado' : 'pendiente',
        ]);
        return response()->json(['importacion' => $import, 'filas_validas' => count($checked['filas'])]);
    }

    public function confirmar(Importacion $importacion, ImportadorRally $parser)
    {
        abort_if($importacion->estado === 'importado', 422, 'La importación ya fue confirmada.');
        DB::transaction(function () use ($importacion, $parser) {
            foreach ($importacion->vista_previa ?? [] as $row) {
                $categoryName = trim((string) ($row['categoria'] ?? ''));
                $category = $categoryName === '' ? null : Categoria::firstOrCreate(
                    ['evento_id' => $importacion->evento_id, 'nombre' => $categoryName],
                    ['nombre_corto' => $categoryName, 'activa' => true]
                );
                $team = empty($row['team']) ? null : Equipo::firstOrCreate(['evento_id' => $importacion->evento_id, 'nombre' => trim($row['team'])]);
                $pilot = Competidor::updateOrCreate(
                    ['evento_id' => $importacion->evento_id, 'numeral' => (int) $row['numero']],
                    ['nombre' => trim($row['nombre']), 'ciudad' => $this->optional($row['ciudad'] ?? null),
                     'pais' => $this->optional($row['pais'] ?? null), 'team' => $team?->nombre, 'equipo_id' => $team?->id,
                     'categoria' => $category?->nombre, 'categoria_id' => $category?->id,
                     'vehiculo' => $this->optional($row['vehiculo'] ?? null), 'copiloto' => $this->optional($row['copiloto'] ?? null),
                     'biografia' => $this->optional($row['biografia'] ?? null), 'destacado' => $this->boolean($row['destacado'] ?? false), 'estado' => 'publicado']
                );
                if ($importacion->tipo === 'resultados') Resultado::updateOrCreate(
                    ['etapa_id' => $importacion->etapa_id, 'numero' => (int) $row['numero']],
                    ['evento_id' => $importacion->evento_id, 'competidor_id' => $pilot->id, 'importacion_id' => $importacion->id,
                     'nombre_piloto' => $pilot->nombre, 'categoria' => $category?->nombre,
                     'tiempo_ms' => $parser->tiempoAMilisegundos($row['tiempo'] ?? null),
                     'penalizacion_ms' => $parser->tiempoAMilisegundos($row['penalizacion'] ?? null) ?? 0,
                     'estado_carrera' => $parser->estado($row['estado'] ?? ''),
                     'observacion' => $row['observacion'] ?? null, 'publicacion' => 'borrador']
                );
            }
            $importacion->update(['estado' => 'importado']);
        });
        return response()->json(['message' => 'Importación confirmada.', 'importacion' => $importacion->fresh()]);
    }

    private function optional(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));
        return $value === '' ? null : $value;
    }

    private function boolean(mixed $value): bool
    {
        return in_array(mb_strtolower(trim((string) $value)), ['1', 'si', 'sí', 'true', 'yes', 'x'], true);
    }
}
