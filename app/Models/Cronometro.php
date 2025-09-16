<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cronometro extends Model
{
    protected $fillable = [
        'evento_id',
        'etapa',
        'inicio',
        'activo',
    ];

    protected $casts = [
        'inicio' => 'datetime:Y-m-d H:i:s',
        'activo' => 'boolean',
    ];

    public function evento()
    {
        return $this->belongsTo(Evento::class, 'evento_id', 'id');
    }
}
