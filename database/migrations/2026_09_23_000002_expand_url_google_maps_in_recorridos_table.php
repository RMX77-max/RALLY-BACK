<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recorridos', function (Blueprint $table) {
            $table->text('url_google_maps')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('recorridos', function (Blueprint $table) {
            $table->string('url_google_maps', 255)->nullable()->change();
        });
    }
};
