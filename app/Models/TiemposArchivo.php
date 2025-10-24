<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TiemposArchivo extends Model
{
    protected $table = 'tiempos_archivos';

    protected $fillable = [
        'user_id',
        'nombre_original',
        'path',
        'columnas',
        'filas',
        'total_filas',
    ];

    protected $casts = [
        'columnas' => 'array',
        'filas'    => 'array',
    ];
}
