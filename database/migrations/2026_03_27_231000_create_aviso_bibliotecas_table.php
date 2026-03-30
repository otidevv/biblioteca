<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aviso_bibliotecas', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 160);
            $table->text('contenido')->nullable();
            $table->string('tipo', 40)->default('noticia');
            $table->string('accion_url')->nullable();
            $table->string('accion_texto', 80)->nullable();
            $table->timestamp('inicio_publicacion')->nullable();
            $table->timestamp('fin_publicacion')->nullable();
            $table->boolean('es_destacado')->default(false);
            $table->tinyInteger('estado')->default(1);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aviso_bibliotecas');
    }
};
