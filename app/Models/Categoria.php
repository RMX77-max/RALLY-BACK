<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $fillable = ['evento_id', 'nombre', 'nombre_corto', 'descripcion', 'imagen', 'icono', 'color', 'orden', 'activa'];
    protected $casts = ['activa' => 'boolean'];
    public function evento() { return $this->belongsTo(Evento::class); }
    public function competidores() { return $this->hasMany(Competidor::class); }
}
