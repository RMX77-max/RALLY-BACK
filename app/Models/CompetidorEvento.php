<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CompetidorEvento extends Model
{
    use HasFactory;

    protected $table = 'competidores_evento';

    protected $fillable = [
        'numero',
        'nombre',
        'categoria',
        'team',
        'foto', // guardas lo que tengas (relativo o absoluto), el accessor lo resuelve
    ];

    protected $casts = [
        'numero' => 'integer',
    ];

    // 👉 para que 'foto_url' se agregue en el JSON automáticamente
    protected $appends = ['foto_url'];

    /**
     * Accessor: siempre devuelve una URL absoluta lista para <img>
     */
    public function getFotoUrlAttribute()
    {
        $path = $this->attributes['foto'] ?? null;
        if (!$path) {
            return null;
        }

        // si ya es absoluta, la devolvemos tal cual
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        // si viene con /storage/... o storage/...
        if (Str::startsWith($path, ['/storage/', 'storage/'])) {
            // asset() ya agrega el dominio base
            return asset(ltrim($path, '/'));
        }

        // caso general: path relativo p.ej. "fotos_competidores/xyz.jpg"
        return asset('storage/' . ltrim($path, '/'));
    }

    /**
     * (Opcional) Mutator para almacenar paths de forma consistente.
     * Si prefieres no tocar lo que guardas, elimina este método.
     */
    public function setFotoAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['foto'] = null;
            return;
        }

        $v = (string) $value;

        // Si viene como URL absoluta (http/https), intenta reducir a un path relativo si apunta a /storage
        if (Str::startsWith($v, ['http://', 'https://'])) {
            // normaliza a sin dominio cuando apunta a /storage/...
            $after = Str::after($v, '/storage/');
            if ($after !== $v) {
                // era una URL a /storage/..., guarda solo el relativo en storage/
                $this->attributes['foto'] = ltrim($after, '/');
                return;
            }
            // si es otra URL externa, guárdala tal cual
            $this->attributes['foto'] = $v;
            return;
        }

        // Si viene como "storage/..." guarda solo el relativo tras "storage/"
        if (Str::startsWith($v, 'storage/')) {
            $this->attributes['foto'] = ltrim(Str::after($v, 'storage/'), '/');
            return;
        }

        // Caso general: ya es relativo tipo "fotos_competidores/..."
        $this->attributes['foto'] = ltrim($v, '/');
    }
}
