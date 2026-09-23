<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resultado extends Model
{
    protected $fillable = ['evento_id', 'etapa_id', 'competidor_id', 'importacion_id', 'numero', 'nombre_piloto', 'categoria', 'tiempo_ms', 'penalizacion_ms', 'posicion', 'estado_carrera', 'observacion', 'publicacion'];
    protected $casts = ['tiempo_ms' => 'integer', 'penalizacion_ms' => 'integer'];
    protected $appends = ['tiempo_formateado', 'tiempo_total_ms'];

    public function evento() { return $this->belongsTo(Evento::class); }
    public function etapa() { return $this->belongsTo(Etapa::class); }
    public function competidor() { return $this->belongsTo(Competidor::class); }

    public function getTiempoTotalMsAttribute(): ?int
    {
        return $this->tiempo_ms === null ? null : $this->tiempo_ms + $this->penalizacion_ms;
    }

    public function getTiempoFormateadoAttribute(): ?string
    {
        if ($this->tiempo_total_ms === null) return null;
        $ms = $this->tiempo_total_ms;
        $hours = intdiv($ms, 3600000);
        $minutes = intdiv($ms % 3600000, 60000);
        $seconds = intdiv($ms % 60000, 1000);
        $millis = $ms % 1000;
        return sprintf('%d:%02d:%02d.%03d', $hours, $minutes, $seconds, $millis);
    }
}
