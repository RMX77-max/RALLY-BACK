<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Etapa extends Model
{
    protected $fillable = ['evento_id', 'numero', 'nombre', 'origen', 'destino', 'distancia_km', 'fecha', 'hora', 'descripcion', 'imagen', 'orden', 'estado'];
    protected $casts = ['fecha' => 'date', 'distancia_km' => 'decimal:2'];
    public function evento() { return $this->belongsTo(Evento::class); }
    public function resultados() { return $this->hasMany(Resultado::class); }
}
