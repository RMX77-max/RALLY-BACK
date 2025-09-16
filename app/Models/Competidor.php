<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Competidor extends Model
{
    protected $table = 'competidores'; // Forzar nombre de tabla
    protected $primaryKey = 'id'; // Definir la PK personalizada
    public $incrementing = true; //  auto-incremento
    protected $keyType = 'int'; // Tipo de la PK

    protected $fillable = [
        'nombre',
        'ciudad',
        'team',
        'categoria',
        'numeral',
        'foto_path',
         'evento_id',
            'orden_largada'
    ];

     protected $appends = ['foto_url'];

    public function getFotoUrlAttribute(): ?string
{
    if (!$this->foto_path) {
        return null;
    }

    // Construye la URL pública directamente: https://TU-APP/storage/lo-que-sea.jpg
    return asset('storage/' . ltrim($this->foto_path, '/'));
}


     public function evento()
    {
        return $this->belongsTo(Evento::class);
    }

    public function tiempos()
{
    return $this->hasMany(Tiempo::class);
}

}
