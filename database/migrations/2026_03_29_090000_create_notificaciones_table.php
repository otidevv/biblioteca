<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('contenido');
            $table->string('tipo')->default('aviso');
            $table->string('canal')->default('interno');
            $table->string('audiencia')->default('personal');
            $table->foreignId('user_id_origen')->nullable()->constrained('users')->nullOnDelete();
            $table->string('accion_url')->nullable();
            $table->string('entidad_tipo')->nullable();
            $table->unsignedBigInteger('entidad_id')->nullable();
            $table->boolean('es_programada')->default(false);
            $table->timestamp('fecha_publicacion')->nullable();
            $table->timestamp('fecha_expiracion')->nullable();
            $table->tinyInteger('estado')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
