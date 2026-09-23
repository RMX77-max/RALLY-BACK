<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('competidores', function (Blueprint $table) {
            $table->string('ciudad')->nullable()->change();
            $table->string('categoria')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('competidores', function (Blueprint $table) {
            $table->string('ciudad')->nullable(false)->change();
            $table->string('categoria')->nullable(false)->change();
        });
    }
};
