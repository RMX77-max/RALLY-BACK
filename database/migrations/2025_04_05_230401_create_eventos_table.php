<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('eventos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('ubicacion')->nullable();
            $table->date('fecha')->nullable();
            $table->text('descripcion')->nullable();

            // Nuevo campo para distinguir el tipo de evento
            $table->enum('tipo_evento', ['simple', 'grande'])
                  ->default('simple');
            // simple = pocos corredores, orden por inscripción
            // con_cupos = grandes eventos, cupos reservados por categoría

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eventos');
    }
};
