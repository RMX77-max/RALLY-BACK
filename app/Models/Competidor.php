<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Competidor extends Model
{
    protected $table = 'competidores'; // Forzar nombre de tabla
    protected $primaryKey = 'ci'; // Definir la PK personalizada
    public $incrementing = false; // Desactivar auto-incremento
    protected $keyType = 'string'; // Tipo de la PK

    protected $fillable = [
        'ci',
        'nombre',
        'ciudad',
        'team',
        'categoria',
        'numeral',
        'tipodesangre',
        'foto_path',
    ];
}
