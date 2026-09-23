<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recorrido extends Model
{
    protected $fillable = ['evento_id', 'imagen_mapa', 'url_google_maps', 'archivo_recorrido', 'descripcion', 'activo'];
    protected $casts = ['activo' => 'boolean'];
    public function evento() { return $this->belongsTo(Evento::class); }
}
