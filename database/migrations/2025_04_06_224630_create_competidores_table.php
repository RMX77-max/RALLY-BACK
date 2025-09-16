<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competidores', function (Blueprint $table) {
            $table->id(); // PK autoincremental
            $table->unsignedBigInteger('evento_id'); // FK al evento

            $table->string('nombre');
            $table->string('ciudad');
            $table->string('team')->nullable();
            $table->string('categoria');
            $table->unsignedInteger('numeral');
            $table->unsignedInteger('orden_largada')->nullable();
            $table->string('foto_path')->nullable(); // ruta de la imagen

            $table->timestamps();

            // Relaciones y restricciones
            $table->foreign('evento_id')
                ->references('id')->on('eventos')
                ->onDelete('cascade');

            // Evita dorsales duplicados dentro del mismo evento
            $table->unique(['evento_id', 'numeral']);

            //Indice para mejorar consultas por evento
            $table->index('evento_id');
            $table->index('orden_largada');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competidores');
    }
};
