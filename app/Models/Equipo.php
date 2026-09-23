<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Equipo extends Model
{
    protected $fillable = ['evento_id', 'nombre', 'logo', 'url', 'orden', 'activo'];
    protected $casts = ['activo' => 'boolean'];
    public function evento() { return $this->belongsTo(Evento::class); }
    public function competidores() { return $this->hasMany(Competidor::class); }
}
