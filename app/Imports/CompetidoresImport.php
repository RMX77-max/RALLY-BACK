<?php

namespace App\Imports;

use App\Models\CompetidorEvento;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CompetidoresImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        $seen = []; // para evitar duplicados dentro del mismo Excel

        foreach ($rows as $row) {
            $numero = (int)($row['numero'] ?? 0);
            if ($numero <= 0) {
                continue; // fila inválida
            }

            // evita repetir el mismo número dentro del mismo archivo
            if (isset($seen[$numero])) {
                continue;
            }
            $seen[$numero] = true;

            CompetidorEvento::updateOrCreate(
                ['numero' => $numero], // clave única
                [
                    'nombre'    => trim((string)($row['nombre'] ?? '')),
                    'categoria' => trim((string)($row['categoria'] ?? '')),
                    'team'      => trim((string)($row['team'] ?? '')),
                    // ojo: NO pasamos 'foto' aquí -> se conserva la existente
                ]
            );
        }
    }
}
