<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasFactory;
    protected $fillable = ['evento_id', 'titulo', 'descripcion', 'url', 'miniatura', 'duracion', 'destacado', 'orden', 'publicado'];
    protected $casts = ['destacado' => 'boolean', 'publicado' => 'boolean'];
}
