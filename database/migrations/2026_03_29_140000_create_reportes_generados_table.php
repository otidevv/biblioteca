<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reportes_generados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('modulo', 80);
            $table->string('formato', 20);
            $table->json('filtros')->nullable();
            $table->string('estado', 20)->default('pendiente');
            $table->string('archivo_nombre')->nullable();
            $table->string('archivo_ruta')->nullable();
            $table->unsignedInteger('total_registros')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('solicitado_en')->nullable();
            $table->timestamp('procesado_en')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reportes_generados');
    }
};
