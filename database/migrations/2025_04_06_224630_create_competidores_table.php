<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('competidores', function (Blueprint $table) {
            $table->string('ci')->primary(); // Clave primaria (sin auto-incremento)
            $table->string('nombre');
            $table->string('ciudad');
            $table->string('team')->nullable();
            $table->string('categoria');
            $table->integer('numeral');
            $table->string('tipodesangre')->nullable();
            $table->string('foto_path')->nullable(); // Ruta de la imagen
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competidores');
    }
};
