<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Importacion extends Model
{
    protected $table = 'importaciones';

    protected $fillable = ['evento_id', 'etapa_id', 'user_id', 'tipo', 'nombre_original', 'archivo', 'columnas', 'vista_previa', 'errores', 'total_filas', 'estado'];
    protected $casts = ['columnas' => 'array', 'vista_previa' => 'array', 'errores' => 'array'];
    public function evento() { return $this->belongsTo(Evento::class); }
    public function etapa() { return $this->belongsTo(Etapa::class); }
}
