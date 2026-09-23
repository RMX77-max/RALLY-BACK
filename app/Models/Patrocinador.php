<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patrocinador extends Model
{
    use HasFactory;
    protected $table = 'patrocinadores';

    protected $fillable = [
        'titulo',
        'descripcion',
        'imagen', 'evento_id', 'url', 'tipo', 'orden', 'activo',
    ];
    protected $casts = ['activo' => 'boolean'];
}
