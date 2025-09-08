<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
    protected $fillable = ['nombre', 'fecha', 'ubicacion'];

    public function competidores()
    {
        return $this->hasMany(Competidor::class);
    }

    public function tiempos()
    {
        return $this->hasMany(Tiempo::class);
    }
}
