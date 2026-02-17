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
        Schema::create('configuracion_tipo_lectores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_lector_id')->constrained('tipo_lectores')->cascadeOnDelete();
            $table->integer('max_prestamos')->default(3);
            $table->integer('dias_prestamo')->default(7);
            $table->decimal('multa_por_dia', 8, 2)->default(1.00);
            $table->boolean('puede_reservar')->default(true);
            $table->boolean('puede_renovar')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracion_tipo_lectores');
    }
};
