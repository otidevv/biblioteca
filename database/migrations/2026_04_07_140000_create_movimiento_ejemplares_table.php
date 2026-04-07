<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimiento_ejemplares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ejemplar_id')->constrained('ejemplares')->cascadeOnDelete();
            $table->foreignId('libro_id')->constrained('libros')->cascadeOnDelete();
            $table->foreignId('biblioteca_origen_id')->nullable()->constrained('bibliotecas');
            $table->foreignId('biblioteca_destino_id')->nullable()->constrained('bibliotecas');
            $table->foreignId('solicitado_por_user_id')->constrained('users');
            $table->foreignId('resuelto_por_user_id')->nullable()->constrained('users');
            $table->string('estado', 20)->default('pendiente');
            $table->timestamp('solicitado_en')->nullable();
            $table->timestamp('resuelto_en')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimiento_ejemplares');
    }
};
