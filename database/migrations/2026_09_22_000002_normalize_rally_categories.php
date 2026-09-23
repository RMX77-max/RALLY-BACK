<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $eventId = DB::table('eventos')->where('activo', true)->value('id');
        if (!$eventId) return;

        $names = [
            1 => 'Open Fuerza Libre',
            2 => '250 XR',
            3 => '185 Mecánica Nacional',
            4 => 'CG',
            5 => 'Damas',
            6 => 'Cuadratracks',
        ];

        $canonical = DB::table('categorias')->where('evento_id', $eventId)->where('orden', 3)->first();
        if (!$canonical) return;

        $duplicates = DB::table('categorias')->where('evento_id', $eventId)
            ->where('id', '!=', $canonical->id)->where('nombre', 'like', '%185%')->get();
        foreach ($duplicates as $duplicate) {
            DB::table('competidores')->where('categoria_id', $duplicate->id)
                ->update(['categoria_id' => $canonical->id, 'categoria' => $names[3]]);
            DB::table('categorias')->where('id', $duplicate->id)->delete();
        }

        foreach ($names as $order => $name) {
            DB::table('categorias')->where('evento_id', $eventId)->where('orden', $order)
                ->update(['nombre' => $name, 'nombre_corto' => $name, 'activa' => true]);
        }
        DB::table('competidores')->where('categoria_id', $canonical->id)
            ->update(['categoria' => $names[3]]);
    }

    public function down(): void {}
};
