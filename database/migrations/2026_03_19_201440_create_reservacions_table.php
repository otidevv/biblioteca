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
        Schema::create('reservaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ejemplar_id')->constrained('ejemplares')->onDelete('cascade');
            $table->foreignId('lector_id')->constrained('users')->onDelete('cascade');
            $table->integer('duracion'); // días
            $table->date('fecha_reservacion');
            $table->date('fecha_limite');
            $table->tinyInteger('prestamo')->default(1);//1 sala, 2 casa
            $table->foreignId('bibliotecario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->tinyInteger('estado')->default(1);// 0 en espera, 1 atendido, 2 cancelado
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservacions');
    }
};
