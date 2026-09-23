<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
    protected $fillable = [
        'nombre', 'edicion', 'fecha', 'fecha_fin', 'ubicacion', 'descripcion',
        'lema', 'texto_introductorio', 'tipo_evento', 'estado', 'activo',
    ];

    protected $casts = [
        'fecha' => 'date',
        'fecha_fin' => 'date',
        'activo' => 'boolean',
    ];

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

    public function categorias() { return $this->hasMany(Categoria::class); }
    public function etapas() { return $this->hasMany(Etapa::class); }
    public function cronograma() { return $this->hasMany(Cronograma::class); }
    public function banners() { return $this->hasMany(Banner::class); }
    public function recorrido() { return $this->hasOne(Recorrido::class); }
    public function equipos() { return $this->hasMany(Equipo::class); }
}
