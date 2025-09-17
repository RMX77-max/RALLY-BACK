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
            $table->unsignedBigInteger('competidor_id'); // Tipo debe coincidir con el tipo de 'ci' en competidores
            $table->unsignedTinyInteger('etapa');
            $table->decimal('tiempo');
            $table->dateTime('fecha_registro');
            $table->unsignedBigInteger('evento_id');
            $table->timestamps();

            // Definición correcta de la foreign key
            $table->foreign('competidor_id')
                  ->references('id')  // Referencia al campo correcto
                  ->on('competidores')
                  ->onDelete('cascade');

            $table->foreign('evento_id')
                    ->references('id')
                    ->on('eventos')
                    ->onDelete('cascade');

            $table->unique(['competidor_id', 'etapa', 'evento_id']);
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
