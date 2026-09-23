<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cronograma extends Model
{
    protected $table = 'cronogramas';
    protected $fillable = ['evento_id', 'fecha', 'hora', 'actividad', 'ubicacion', 'descripcion', 'orden', 'activo'];
    protected $casts = ['fecha' => 'date', 'activo' => 'boolean'];
    public function evento() { return $this->belongsTo(Evento::class); }
}
