<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FotoRally extends Model
{
    // 👇 Esto corrige el nombre de la tabla
    protected $table = 'fotos_rally';

    protected $fillable = ['ruta'];

    public $timestamps = true; // o false, según tu migración
}
