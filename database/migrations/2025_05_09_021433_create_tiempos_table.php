<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // database/migrations/[timestamp]_create_tiempos_table.php
    public function up()
    {
        Schema::create('tiempos', function (Blueprint $table) {
            $table->id();
            $table->string('competidor_ci'); // Tipo debe coincidir con el tipo de 'ci' en competidores
            $table->integer('etapa');
            $table->string('tiempo');
            $table->dateTime('fecha_registro');
            $table->timestamps();

            // Definición correcta de la foreign key
            $table->foreign('competidor_ci')
                  ->references('ci')  // Referencia al campo correcto
                  ->on('competidores')
                  ->onDelete('cascade');

            $table->unique(['competidor_ci', 'etapa']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tiempos');
    }
};
