<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Competidor;
use App\Models\Cronograma;
use App\Models\Etapa;
use App\Models\Evento;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Rally2026Seeder extends Seeder
{
    public function run(): void
    {
        $evento = Evento::updateOrCreate(
            ['nombre' => 'Rally Comarapa 2026'],
            [
                'edicion' => 2026, 'fecha' => '2026-06-12', 'fecha_fin' => '2026-06-14',
                'ubicacion' => 'Comarapa, Santa Cruz', 'descripcion' => 'Rally de motos en la tierra y montaña de Comarapa.',
                'lema' => 'Más que una carrera, es nuestra tierra', 'tipo_evento' => 'grande',
                'estado' => 'publicado', 'activo' => true,
            ]
        );
        Evento::where('id', '!=', $evento->id)->update(['activo' => false]);

        foreach (['Motos 450', 'Motos 250 XR', 'Motos 185 Mec Nacional', 'Motos CG', 'Damas', 'Cuadratracks'] as $order => $name) {
            Categoria::updateOrCreate(['evento_id' => $evento->id, 'nombre' => $name], ['nombre_corto' => $name, 'orden' => $order + 1, 'activa' => true]);
        }

        $stages = [
            [1, 'Comarapa – La Ciénega', 'Comarapa', 'La Ciénega', 28.4, '2026-06-13', '07:30'],
            [2, 'La Angostura – Mataral', 'La Angostura', 'Mataral', 32.1, '2026-06-13', '12:30'],
            [3, 'Mataral – Comarapa', 'Mataral', 'Comarapa', 26.7, '2026-06-14', '07:30'],
            [4, 'El Trigal – Comarapa', 'El Trigal', 'Comarapa', 24.9, '2026-06-14', '12:00'],
        ];
        foreach ($stages as [$number, $name, $from, $to, $distance, $date, $time]) {
            Etapa::updateOrCreate(['evento_id' => $evento->id, 'numero' => $number], ['nombre' => $name, 'origen' => $from, 'destino' => $to, 'distancia_km' => $distance, 'fecha' => $date, 'hora' => $time, 'orden' => $number]);
        }

        $schedule = [
            ['2026-06-12', '08:00', 'Verificaciones técnicas y administrativas', 'Plaza Principal - Comarapa'],
            ['2026-06-12', '14:00', 'Pruebas libres', 'Sector La Ciénega'],
            ['2026-06-12', '19:00', 'Reunión de pilotos', 'Salón Municipal'],
            ['2026-06-13', '07:30', 'Largada Etapa 1', 'Comarapa - La Ciénega'],
            ['2026-06-13', '12:30', 'Largada Etapa 2', 'La Angostura - Mataral'],
            ['2026-06-14', '07:30', 'Largada Etapa 3', 'Mataral - Comarapa'],
            ['2026-06-14', '12:00', 'Largada Etapa 4', 'El Trigal - Comarapa'],
            ['2026-06-14', '16:00', 'Premiación', 'Plaza Principal - Comarapa'],
        ];
        foreach ($schedule as $order => [$date, $time, $activity, $location]) {
            Cronograma::firstOrCreate(['evento_id' => $evento->id, 'fecha' => $date, 'hora' => $time, 'actividad' => $activity], ['ubicacion' => $location, 'orden' => $order + 1]);
        }

        if (Schema::hasTable('competidores_evento')) {
            foreach (DB::table('competidores_evento')->get() as $legacy) {
                $category = Categoria::firstOrCreate(['evento_id' => $evento->id, 'nombre' => $legacy->categoria ?: 'Sin categoría']);
                Competidor::updateOrCreate(
                    ['evento_id' => $evento->id, 'numeral' => (int) $legacy->numero],
                    ['nombre' => $legacy->nombre, 'ciudad' => 'Sin registrar', 'team' => $legacy->team,
                     'categoria' => $category->nombre, 'categoria_id' => $category->id, 'foto_path' => $legacy->foto, 'estado' => 'publicado']
                );
            }
        }
    }
}
