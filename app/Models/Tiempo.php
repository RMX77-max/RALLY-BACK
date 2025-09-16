<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tiempo extends Model
{
    use HasFactory;

    protected $fillable = [
        'competidor_id',
        'etapa',
        'tiempo',
        'fecha_registro',
        'evento_id'
    ];

    protected $casts = [
        'fecha_registro' => 'datetime:Y-m-d H:i:s',
        'created_at'     => 'datetime:Y-m-d H:i:s',
        'updated_at'     => 'datetime:Y-m-d H:i:s'
    ];

    /**
     * Relación con el modelo Competidor
     */
    public function competidor()
    {
        return $this->belongsTo(Competidor::class, 'competidor_id', 'id');
    }

    /**
     * Relación con el modelo Evento
     */
    public function evento()
    {
        return $this->belongsTo(Evento::class);
    }
}
