<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
    protected $fillable = ['nombre', 'fecha', 'ubicacion', 'descripcion', 'tipo_evento'];

    public function competidores()
    {
        return $this->hasMany(Competidor::class);
    }

    public function tiempos()
    {
        return $this->hasMany(Tiempo::class);
    }

    public function cuposCategorias()
    {
        return $this->hasMany(CupoCategoria::class);
    }
}
