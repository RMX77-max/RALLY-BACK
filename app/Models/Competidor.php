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
        ,'categoria_id', 'equipo_id', 'pais', 'vehiculo', 'copiloto',
        'biografia', 'frase', 'foto_portada', 'destacado', 'orden', 'estado'
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
    return $this->hasMany(Tiempo::class, 'competidor_id', 'id');
}

    public function categoriaRelacion() { return $this->belongsTo(Categoria::class, 'categoria_id'); }
    public function equipo() { return $this->belongsTo(Equipo::class); }
    public function resultados() { return $this->hasMany(Resultado::class); }

    protected $casts = ['destacado' => 'boolean'];

}
