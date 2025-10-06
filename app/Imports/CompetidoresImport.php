<?php

namespace App\Imports;

use App\Models\CompetidorEvento;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CompetidoresImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new CompetidorEvento([
            'nombre'    => $row['nombre'] ?? '',
            'categoria' => $row['categoria'] ?? '',
            'numero'    => $row['numero'] ?? '',
            'team'      => $row['team'] ?? '',
            'foto'      => null, // se agregará luego desde el front
        ]);
    }
}
