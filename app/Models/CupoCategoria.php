<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CupoCategoria extends Model
{
    protected $table = 'cupos_categorias';

    protected $fillable = [
        'evento_id',
        'categoria',
        'cupos',
    ];

    public function evento()
    {
        return $this->belongsTo(Evento::class);
    }
}
