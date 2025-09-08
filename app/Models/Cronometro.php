<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cronometro extends Model
{
    protected $fillable = ['evento_id', 'etapa', 'inicio', 'activo'];
}

