<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tiempo extends Model
{
    use HasFactory;

    protected $fillable = [
        'competidor_ci',
        'etapa',
        'tiempo',
        'fecha_registro'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'fecha_registro' => 'datetime:Y-m-d H:i:s', // Formato específico para fecha_registro
        'created_at' => 'datetime:Y-m-d H:i:s',     // Formato para creación
        'updated_at' => 'datetime:Y-m-d H:i:s'      // Formato para actualización
    ];

    // Opcional: Forzar tipo string para CI
    public function setCompetidorCiAttribute($value)
    {
        $this->attributes['competidor_ci'] = (string)$value;
    }

    /**
     * Relación con el modelo Competidor
     */
    public function competidor()
    {
        return $this->belongsTo(Competidor::class, 'competidor_ci', 'ci');
    }
}
