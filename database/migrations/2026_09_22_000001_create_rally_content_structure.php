<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            $table->date('fecha_fin')->nullable()->after('fecha');
            $table->unsignedSmallInteger('edicion')->nullable()->after('nombre');
            $table->string('lema')->nullable()->after('descripcion');
            $table->text('texto_introductorio')->nullable()->after('lema');
            $table->enum('estado', ['borrador', 'publicado', 'finalizado'])->default('borrador');
            $table->boolean('activo')->default(false)->index();
        });

        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained('eventos')->cascadeOnDelete();
            $table->string('nombre');
            $table->string('nombre_corto')->nullable();
            $table->text('descripcion')->nullable();
            $table->string('imagen')->nullable();
            $table->string('icono')->nullable();
            $table->string('color', 20)->nullable();
            $table->unsignedInteger('orden')->default(0);
            $table->boolean('activa')->default(true);
            $table->timestamps();
            $table->unique(['evento_id', 'nombre']);
        });

        Schema::create('equipos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->nullable()->constrained('eventos')->nullOnDelete();
            $table->string('nombre');
            $table->string('logo')->nullable();
            $table->string('url')->nullable();
            $table->unsignedInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->unique(['evento_id', 'nombre']);
        });

        Schema::create('etapas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained('eventos')->cascadeOnDelete();
            $table->unsignedInteger('numero');
            $table->string('nombre');
            $table->string('origen')->nullable();
            $table->string('destino')->nullable();
            $table->decimal('distancia_km', 8, 2)->nullable();
            $table->date('fecha')->nullable();
            $table->time('hora')->nullable();
            $table->text('descripcion')->nullable();
            $table->string('imagen')->nullable();
            $table->unsignedInteger('orden')->default(0);
            $table->enum('estado', ['programada', 'en_disputa', 'completada', 'cancelada'])->default('programada');
            $table->timestamps();
            $table->unique(['evento_id', 'numero']);
        });

        Schema::create('cronogramas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained('eventos')->cascadeOnDelete();
            $table->date('fecha');
            $table->time('hora')->nullable();
            $table->string('actividad');
            $table->string('ubicacion')->nullable();
            $table->text('descripcion')->nullable();
            $table->unsignedInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->nullable()->constrained('eventos')->cascadeOnDelete();
            $table->string('seccion');
            $table->string('titulo')->nullable();
            $table->string('subtitulo')->nullable();
            $table->string('imagen_escritorio');
            $table->string('imagen_movil')->nullable();
            $table->string('texto_boton')->nullable();
            $table->string('enlace_boton')->nullable();
            $table->unsignedInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('recorridos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained('eventos')->cascadeOnDelete();
            $table->string('imagen_mapa')->nullable();
            $table->string('url_google_maps')->nullable();
            $table->string('archivo_recorrido')->nullable();
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->unique('evento_id');
        });

        Schema::table('competidores', function (Blueprint $table) {
            $table->foreignId('categoria_id')->nullable()->after('evento_id')->constrained('categorias')->nullOnDelete();
            $table->foreignId('equipo_id')->nullable()->after('categoria_id')->constrained('equipos')->nullOnDelete();
            $table->string('pais')->nullable()->after('ciudad');
            $table->string('vehiculo')->nullable()->after('team');
            $table->string('copiloto')->nullable()->after('vehiculo');
            $table->text('biografia')->nullable()->after('foto_path');
            $table->string('frase')->nullable()->after('biografia');
            $table->string('foto_portada')->nullable()->after('frase');
            $table->boolean('destacado')->default(false)->index();
            $table->unsignedInteger('orden')->default(0);
            $table->enum('estado', ['borrador', 'publicado', 'inactivo'])->default('publicado');
        });

        Schema::create('importaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained('eventos')->cascadeOnDelete();
            $table->foreignId('etapa_id')->nullable()->constrained('etapas')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('tipo', ['pilotos', 'resultados']);
            $table->string('nombre_original');
            $table->string('archivo');
            $table->json('columnas')->nullable();
            $table->json('vista_previa')->nullable();
            $table->json('errores')->nullable();
            $table->unsignedInteger('total_filas')->default(0);
            $table->enum('estado', ['pendiente', 'validado', 'importado', 'rechazado'])->default('pendiente');
            $table->timestamps();
        });

        Schema::create('resultados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained('eventos')->cascadeOnDelete();
            $table->foreignId('etapa_id')->constrained('etapas')->cascadeOnDelete();
            $table->foreignId('competidor_id')->nullable()->constrained('competidores')->nullOnDelete();
            $table->foreignId('importacion_id')->nullable()->constrained('importaciones')->nullOnDelete();
            $table->unsignedInteger('numero');
            $table->string('nombre_piloto');
            $table->string('categoria');
            $table->unsignedBigInteger('tiempo_ms')->nullable();
            $table->unsignedBigInteger('penalizacion_ms')->default(0);
            $table->unsignedInteger('posicion')->nullable();
            $table->enum('estado_carrera', ['finalizo', 'abandono', 'no_largo', 'descalificado'])->default('finalizo');
            $table->text('observacion')->nullable();
            $table->enum('publicacion', ['borrador', 'provisional', 'oficial', 'oculto'])->default('borrador');
            $table->timestamps();
            $table->unique(['etapa_id', 'numero']);
            $table->index(['evento_id', 'publicacion']);
        });

        Schema::table('galerias', function (Blueprint $table) {
            $table->foreignId('evento_id')->nullable()->after('id')->constrained('eventos')->nullOnDelete();
            $table->string('categoria')->nullable()->after('descripcion');
            $table->string('autor')->nullable()->after('categoria');
            $table->date('fecha_captura')->nullable()->after('autor');
            $table->boolean('destacada')->default(false);
            $table->unsignedInteger('orden')->default(0);
            $table->boolean('publicada')->default(true);
        });

        Schema::table('videos', function (Blueprint $table) {
            $table->foreignId('evento_id')->nullable()->after('id')->constrained('eventos')->nullOnDelete();
            $table->string('miniatura')->nullable()->after('url');
            $table->string('duracion', 20)->nullable()->after('miniatura');
            $table->boolean('destacado')->default(false);
            $table->unsignedInteger('orden')->default(0);
            $table->boolean('publicado')->default(true);
        });

        Schema::table('patrocinadores', function (Blueprint $table) {
            $table->foreignId('evento_id')->nullable()->after('id')->constrained('eventos')->nullOnDelete();
            $table->string('url')->nullable()->after('imagen');
            $table->enum('tipo', ['patrocinador', 'institucion', 'colaborador'])->default('patrocinador');
            $table->unsignedInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
        });

        DB::table('eventos')->orderBy('fecha', 'desc')->limit(1)->update(['activo' => true]);
    }

    public function down(): void
    {
        Schema::table('patrocinadores', function (Blueprint $table) {
            $table->dropConstrainedForeignId('evento_id');
            $table->dropColumn(['url', 'tipo', 'orden', 'activo']);
        });
        Schema::table('videos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('evento_id');
            $table->dropColumn(['miniatura', 'duracion', 'destacado', 'orden', 'publicado']);
        });
        Schema::table('galerias', function (Blueprint $table) {
            $table->dropConstrainedForeignId('evento_id');
            $table->dropColumn(['categoria', 'autor', 'fecha_captura', 'destacada', 'orden', 'publicada']);
        });
        Schema::dropIfExists('resultados');
        Schema::dropIfExists('importaciones');
        Schema::table('competidores', function (Blueprint $table) {
            $table->dropConstrainedForeignId('categoria_id');
            $table->dropConstrainedForeignId('equipo_id');
            $table->dropColumn(['pais', 'vehiculo', 'copiloto', 'biografia', 'frase', 'foto_portada', 'destacado', 'orden', 'estado']);
        });
        Schema::dropIfExists('recorridos');
        Schema::dropIfExists('banners');
        Schema::dropIfExists('cronogramas');
        Schema::dropIfExists('etapas');
        Schema::dropIfExists('equipos');
        Schema::dropIfExists('categorias');
        Schema::table('eventos', function (Blueprint $table) {
            $table->dropColumn(['fecha_fin', 'edicion', 'lema', 'texto_introductorio', 'estado', 'activo']);
        });
    }
};
