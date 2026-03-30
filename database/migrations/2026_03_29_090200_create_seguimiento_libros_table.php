<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seguimiento_libros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('libro_id')->constrained('libros')->cascadeOnDelete();
            $table->string('motivo')->nullable();
            $table->tinyInteger('estado')->default(1);
            $table->boolean('notificado_disponibilidad')->default(false);
            $table->timestamp('fecha_notificacion')->nullable();
            $table->timestamp('ultima_busqueda')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'libro_id'], 'seguimiento_libro_user_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seguimiento_libros');
    }
};
