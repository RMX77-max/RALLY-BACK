<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompetidorEvento extends Model
{
    use HasFactory;

    protected $table = 'competidores_evento';

    protected $fillable = [
        'nombre',
        'categoria',
        'numero',
        'team',
        'foto',
    ];
}
