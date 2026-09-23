<?php
namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportadorRally
{
    private const ALIASES = [
        'numero' => ['numero', 'nro', 'num', 'dorsal', 'numeral', 'competition_number'],
        'nombre' => ['nombre', 'piloto', 'competidor', 'corredor', 'full_name'],
        'categoria' => ['categoria', 'clase'],
        'team' => ['team', 'equipo', 'escuderia'],
        'vehiculo' => ['vehiculo', 'moto', 'modelo'],
        'ciudad' => ['ciudad', 'localidad'], 'pais' => ['pais'],
        'biografia' => ['biografia', 'descripcion', 'perfil'],
        'destacado' => ['destacado', 'principal'],
        'copiloto' => ['copiloto', 'navegante'],
        'tiempo' => ['tiempo', 'tiempo_etapa', 'marca'],
        'penalizacion' => ['penalizacion', 'penalidad'],
        'estado' => ['estado', 'situacion'],
        'observacion' => ['observacion', 'observaciones', 'nota'],
    ];

    public function leer(UploadedFile $archivo): array
    {
        $rows = IOFactory::load($archivo->getRealPath())->getActiveSheet()->toArray(null, true, true, false);
        if (count($rows) < 2) return ['columnas' => [], 'filas' => []];
        $headers = array_map(fn ($v) => $this->normalizar((string) $v), array_shift($rows));
        $map = [];
        foreach ($headers as $i => $header) foreach (self::ALIASES as $field => $aliases) {
            if (in_array($header, $aliases, true)) $map[$i] = $field;
        }
        $data = [];
        foreach ($rows as $row) {
            if (!array_filter($row, fn ($v) => $v !== null && $v !== '')) continue;
            $item = [];
            foreach ($map as $i => $field) $item[$field] = $row[$i] ?? null;
            $data[] = $item;
        }
        return ['columnas' => array_values(array_unique($map)), 'filas' => $data];
    }

    public function validar(array $rows, string $type): array
    {
        $errors = []; $valid = []; $seenNumbers = [];
        foreach ($rows as $index => $row) {
            $missing = [];
            $required = $type === 'pilotos' ? ['numero', 'nombre'] : ['numero', 'nombre', 'categoria'];
            foreach ($required as $field) if (($row[$field] ?? '') === '') $missing[] = $field;
            if ($type === 'resultados' && ($row['tiempo'] ?? '') === '' && $this->estado($row['estado'] ?? '') === 'finalizo') $missing[] = 'tiempo';
            if ($missing) { $errors[] = ['fila' => $index + 2, 'mensaje' => 'Faltan: '.implode(', ', $missing)]; continue; }
            if (!is_numeric($row['numero'])) { $errors[] = ['fila' => $index + 2, 'mensaje' => 'El número debe ser numérico']; continue; }
            if ($type === 'pilotos') {
                $pilotNumber = (int) $row['numero'];
                if (isset($seenNumbers[$pilotNumber])) {
                    $errors[] = [
                        'fila' => $index + 2,
                        'mensaje' => "El numero {$pilotNumber} esta repetido en el archivo (tambien aparece en la fila {$seenNumbers[$pilotNumber]})",
                    ];
                    continue;
                }
                $seenNumbers[$pilotNumber] = $index + 2;
            }
            if ($type === 'resultados' && ($row['tiempo'] ?? '') !== '' && $this->tiempoAMilisegundos($row['tiempo']) === null) {
                $errors[] = ['fila' => $index + 2, 'mensaje' => 'Formato de tiempo no reconocido']; continue;
            }
            $valid[] = $row;
        }
        return ['filas' => $valid, 'errores' => $errors];
    }

    public function tiempoAMilisegundos(mixed $value): ?int
    {
        if ($value === null || $value === '') return null;
        if (is_numeric($value)) { $n = (float) $value; return $n < 1 ? (int) round($n * 86400000) : (int) round($n * 1000); }
        $text = trim(str_replace(',', '.', (string) $value));
        if (!preg_match('/^(?:(\d+):)?(\d{1,2}):(\d{1,2})(?:\.(\d{1,3}))?$/', $text, $p)) return null;
        return ((((int) ($p[1] ?? 0) * 3600) + ((int) $p[2] * 60) + (int) $p[3]) * 1000) + (int) str_pad($p[4] ?? '0', 3, '0');
    }

    public function estado(mixed $value): string
    {
        return match ($this->normalizar((string) $value)) {
            'abandono', 'dnf' => 'abandono', 'no_largo', 'dns' => 'no_largo',
            'descalificado', 'dsq' => 'descalificado', default => 'finalizo',
        };
    }

    private function normalizar(string $value): string
    {
        return (string) Str::of(Str::ascii(trim($value)))->lower()->replaceMatches('/[^a-z0-9]+/', '_')->trim('_');
    }
}
