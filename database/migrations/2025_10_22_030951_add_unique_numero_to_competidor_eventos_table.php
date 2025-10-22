<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('competidor_eventos') && ! $this->indexExists()) {
            Schema::table('competidor_eventos', function (Blueprint $table) {
                $table->unique('numero'); // o $table->unique(['evento_id', 'numero']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('competidor_eventos') && $this->indexExists()) {
            Schema::table('competidor_eventos', function (Blueprint $table) {
                $table->dropUnique(['numero']);
                // si usaste compuesto: $table->dropUnique('competidor_eventos_evento_id_numero_unique');
            });
        }
    }

    private function indexExists(): bool
    {
        // Pequeño helper para evitar doble creación al re-intentar
        $schema = Schema::getConnection()->getDoctrineSchemaManager();
        $indexes = $schema->listTableIndexes('competidor_eventos');
        return array_key_exists('competidor_eventos_numero_unique', $indexes);
    }
};

