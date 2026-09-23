<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = ['evento_id', 'seccion', 'titulo', 'subtitulo', 'imagen_escritorio', 'imagen_movil', 'texto_boton', 'enlace_boton', 'orden', 'activo'];
    protected $casts = ['activo' => 'boolean'];
    public function evento() { return $this->belongsTo(Evento::class); }
}
