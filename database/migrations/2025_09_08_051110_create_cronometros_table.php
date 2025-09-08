<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCronometrosTable extends Migration
{
    public function up()
    {
        Schema::create('cronometros', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('evento_id');
            $table->unsignedTinyInteger('etapa'); // etapa 1 a 6
            $table->timestamp('inicio')->nullable(); // cuando se inició
            $table->boolean('activo')->default(false); // si el cronómetro está corriendo
            $table->timestamps();

            $table->unique(['evento_id', 'etapa']); // solo un cronómetro por evento y etapa
            $table->foreign('evento_id')->references('id')->on('eventos')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cronometros');
    }
}

