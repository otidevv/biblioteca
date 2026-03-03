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
        Schema::create('libros', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('codigo_dewey')->nullable();
            $table->string('isbn')->nullable();
            $table->string('titulo');
            $table->integer('paginas')->nullable();
            $table->date('fecha_publicacion')->nullable();
            $table->string('lugar_publicacion')->nullable();
            $table->text('resumen')->nullable();
            $table->string('archivo_indice')->nullable();
            $table->string('imagen')->nullable();
            $table->string('edicion')->nullable();
            $table->integer('anio_edicion')->nullable();
            $table->string('idioma')->nullable();
            $table->text('anotaciones')->nullable();

            $table->foreignId('editorial_id')->nullable()->constrained("editoriales");
            $table->foreignId('tipo_registro_id')->nullable()->constrained("tipo_registros");

            $table->boolean('estado')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('libros');
    }
};
