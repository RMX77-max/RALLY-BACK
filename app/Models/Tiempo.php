<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tiempo extends Model
{
    protected $fillable = [
        'competidor_id',
        'etapa',
        'tiempo',
        'fecha_registro',
        'evento_id',
    ];

    protected $casts = [
        'tiempo' => 'decimal:3', // lo guardamos con milisegundos
        'fecha_registro' => 'datetime',
    ];

    public function evento()
    {
        return $this->belongsTo(Evento::class);
    }

    public function competidor()
    {
        return $this->belongsTo(Competidor::class, 'competidor_id', 'id');
    }
}
