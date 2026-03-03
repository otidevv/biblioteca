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
        Schema::create('ejemplares', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_interno')->unique();
            $table->foreignId('libro_id')->constrained();
            $table->foreignId('biblioteca_id')->nullable()->constrained();
            $table->foreignId('compra_detalle_id')->constrained('compra_detalles');
            $table->enum('estado',['DISPONIBLE','PRESTADO','BAJA'])->default('DISPONIBLE');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ejemplares');
    }
};
