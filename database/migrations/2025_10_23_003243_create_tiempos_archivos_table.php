<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_tiempos_archivos_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tiempos_archivos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // quién subió
            $table->string('nombre_original');
            $table->string('path');                // storage path del archivo
            $table->json('columnas')->nullable();  // headers (array de strings)
            $table->json('filas')->nullable();     // filas (array de objetos)
            $table->unsignedInteger('total_filas')->default(0);
            $table->timestamps();

            // Si tienes users:
            // $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void {
        Schema::dropIfExists('tiempos_archivos');
    }
};

