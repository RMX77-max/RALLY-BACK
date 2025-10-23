<?php

namespace App\Imports;

use App\Models\CompetidorEvento;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;

class CompetidoresImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        $categoriaActual = null;
        $upserts = 0;
        $saltadas = 0;

        foreach ($rows as $i => $row) {
            // A=0, B=1, C=2
            $colB = isset($row[1]) ? trim((string)$row[1]) : '';
            $colC = isset($row[2]) ? trim((string)$row[2]) : '';

            if ($colB === '' && $colC === '') {
                $saltadas++;
                continue;
            }

            // 1) Detectar cambio de categoría por "CAT. ... " en C
            if ($colC !== '' && preg_match('/^\s*cat\.\s*(.+)$/i', $colC, $m)) {
                $categoriaActual = trim($m[1]);
                continue;
            }

            // 2) Extraer NÚMERO de MÁQUINA
            //    Preferimos el prefijo de la columna C: "007- ...", "8- ...", "05. ...", etc.
            $numero = 0;
            if ($colC !== '' && preg_match('/^\s*0*(\d{1,5})\s*[-–.\s]/', $colC, $mNum)) {
                $numero = (int) $mNum[1];
            } else {
                // Fallback: dígitos puros de la columna B
                $numero = (int) preg_replace('/\D+/', '', $colB);
            }

            if ($numero <= 0) {
                $saltadas++;
                Log::info('[Import Competidores] Sin número válido. Fila=' . ($i + 1) . " B='{$colB}' C='{$colC}'");
                continue;
            }

            // 3) Limpiar texto de C quitando el prefijo numérico para obtener nombre+team
            $texto = $colC;
            $texto = preg_replace('/^\s*0*\d+\s*[-–.\s]\s*/', '', $texto) ?? $texto;
            $texto = trim($texto);

            // 4) Separar NOMBRE y TEAM
            $nombre = $texto;
            $team   = '';

            if ($texto !== '') {
                // Si hay "TEAM"/"Team"/"team": todo lo que sigue es el equipo
                if (preg_match('/\bteam\b/i', $texto)) {
                    [$izq, $der] = preg_split('/\bteam\b/i', $texto, 2);
                    $nombre = trim(rtrim($izq, "-– ."));
                    $resto  = trim($der, " -–.\t\n\r\0\x0B");
                    $team   = $resto === '' ? '' : ('Team ' . $resto);
                } elseif (strpos($texto, '-') !== false) {
                    // Sin "team" pero con guion: lo de la derecha lo tratamos como equipo
                    [$izq, $der] = explode('-', $texto, 2);
                    $nombre = trim($izq);
                    $team   = trim($der);
                } else {
                    // Solo nombre
                    $nombre = trim($texto);
                }
            }

            if ($nombre === '') {
                $saltadas++;
                Log::info('[Import Competidores] Sin nombre. Fila=' . ($i + 1) . " C='{$colC}'");
                continue;
            }

            // 5) Upsert por número (conserva foto)
            CompetidorEvento::updateOrCreate(
                ['numero' => $numero],
                [
                    'nombre'    => $nombre,
                    'categoria' => $categoriaActual ?? '',
                    'team'      => $team,
                    // 'foto' no se toca
                ]
            );
            $upserts++;
        }

        Log::info("[Import Competidores] Finalizado. upserts={$upserts} saltadas={$saltadas}");
    }
}
