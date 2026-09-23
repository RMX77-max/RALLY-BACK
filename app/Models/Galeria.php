<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Galeria extends Model
{
    use HasFactory;
    protected $fillable = ['evento_id', 'titulo', 'descripcion', 'categoria', 'autor', 'fecha_captura', 'imagen', 'destacada', 'orden', 'publicada'];
    protected $casts = ['fecha_captura' => 'date', 'destacada' => 'boolean', 'publicada' => 'boolean'];
}
